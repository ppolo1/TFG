<?php

ini_set('display_errors', 1);
ini_set('controller_override', 1);
error_reporting(E_ALL);

require_once "BBDD.php";

class Libro
{
    // Conexión PDO en $this->db
    protected $db;

    public function __construct()
    {
        // Intentar reutilizar BBDD si está disponible y expone $db
        if (class_exists('BBDD')) {
            try {
                $bbdd = new BBDD();
                if (isset($bbdd->db) && $bbdd->db instanceof PDO) {
                    $this->db = $bbdd->db;
                    return;
                }
                // Si BBDD tiene método connect y deja $db en this, intentar acceder
                if (method_exists($bbdd, 'connect')) {
                    $bbdd->connect();
                    if (isset($bbdd->db) && $bbdd->db instanceof PDO) {
                        $this->db = $bbdd->db;
                        return;
                    }
                }
            } catch (Exception $e) {
                // continuar con fallback
            }
        }

        // Fallback: crear conexión PDO local
        $dbHost = '127.0.0.1';
        $dbName = 'tfg';
        $dbUser = 'root';
        $dbPass = '';
        try {
            $this->db = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (Exception $e) {
            $this->db = null;
        }

        // asegurar que la columna is_admin exista en la tabla usuarios
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'usuarios' AND column_name = 'is_admin'");
            $stmt->execute();
            $r = $stmt->fetch();
            if (! $r || intval($r['cnt']) === 0) {
                // añadir la columna de forma segura
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
                // opcional: marcar al usuario id=1 como admin (si existe)
                $this->db->exec("UPDATE usuarios SET is_admin = 1 WHERE id = 1");
            }
        } catch (\Exception $e) {
            // no bloquear la aplicación si falla la comprobación
        }
    }

    // Devuelve todos los libros (alias getLibros / getAll)
    public function getLibros(): array
    {
        return $this->getAll();
    }

    public function getAll(): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->query('SELECT id, titulo, autor, genero, sinopsis, ejemplares, img FROM libros ORDER BY id ASC');
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Obtiene un libro por id
    public function getById(int $id)
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare('SELECT id, titulo, autor, genero, sinopsis, ejemplares, img FROM libros WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Aliases
    public function get(int $id) { return $this->getById($id); }
    public function find(int $id) { return $this->getById($id); }
    public function detalle(int $id) { return $this->getById($id); }
    public function obtener(int $id) { return $this->getById($id); }

    // Obtiene los libros comprados por un usuario (usa tabla purchases)
    public function getComprados(int $userId): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare('
                SELECT DISTINCT l.id, l.titulo, l.autor, l.img
                FROM libros l
                INNER JOIN purchases p ON l.id = p.book_id
                WHERE p.user_id = ?
                ORDER BY p.purchased_at DESC
            ');
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Aliases para compatibilidad
    public function getPurchased(int $userId): array { return $this->getComprados($userId); }
    public function getByUser(int $userId): array { return $this->getComprados($userId); }

    // Modifica ejemplares: por defecto decrementa en 1. Devuelve el libro actualizado o false.
    public function ejemplares(int $id, int $delta = -1)
    {
        if (!$this->db) return false;
        try {
            $this->db->beginTransaction();

            // asegurar existencia y stock actual
            $stmt = $this->db->prepare('SELECT ejemplares FROM libros WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) {
                $this->db->rollBack();
                return false;
            }
            $current = (int)$row['ejemplares'];
            $new = $current + $delta;
            if ($new < 0) {
                $this->db->rollBack();
                return false;
            }

            $upd = $this->db->prepare('UPDATE libros SET ejemplares = ? WHERE id = ?');
            $upd->execute([$new, $id]);

            // obtener y devolver libro actualizado
            $stmt2 = $this->db->prepare('SELECT id, titulo, autor, genero, sinopsis, ejemplares, img FROM libros WHERE id = ?');
            $stmt2->execute([$id]);
            $book = $stmt2->fetch();

            $this->db->commit();
            return $book ?: false;
        } catch (Exception $e) {
            if ($this->db && $this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    // Registra una compra: decrementa stock y añade registro en purchases. Devuelve true/false.
    public function addPurchase(?int $userId, int $bookId): bool
    {
        if (!$this->db) return false;
        try {
            $this->db->beginTransaction();

            // bloquear fila libro
            $stmt = $this->db->prepare('SELECT ejemplares FROM libros WHERE id = ? FOR UPDATE');
            $stmt->execute([$bookId]);
            $row = $stmt->fetch();
            if (!$row) { $this->db->rollBack(); return false; }
            $stock = (int)$row['ejemplares'];
            if ($stock <= 0) { $this->db->rollBack(); return false; }

            // decrementar
            $upd = $this->db->prepare('UPDATE libros SET ejemplares = ejemplares - 1 WHERE id = ?');
            $upd->execute([$bookId]);

            // crear purchases si no existe (silencioso)
            $this->db->exec("CREATE TABLE IF NOT EXISTS purchases (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                book_id INT NOT NULL,
                purchased_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // insertar compra
            $ins = $this->db->prepare('INSERT INTO purchases (user_id, book_id) VALUES (?, ?)');
            $ins->execute([$userId > 0 ? $userId : null, $bookId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db && $this->db->inTransaction()) $this->db->rollBack();
            return false;
        }
    }

    // Migrar compras guardadas en sesión a la cuenta del usuario.
    // sessionPurchases: array de entradas con al menos ['id'=>bookId] (opcional titulo/autor/img).
    // Para cada bookId:
    //  - si ya existe purchase para este user & book, saltar
    //  - si existe purchase con user_id IS NULL & book_id = X => UPDATE set user_id = $userId
    //  - si no existe ninguna, INSERT una fila purchases (sin decrementar stock porque la compra local ya debería haber decrementado stock cuando se produjo)
    // Devuelve número de compras migradas.
    public function migrateSessionPurchases(int $userId, array $sessionPurchases): int
    {
        if (!$this->db || $userId <= 0) return 0;
        $migrated = 0;
        try {
            foreach ($sessionPurchases as $p) {
                $bookId = intval($p['id'] ?? 0);
                if ($bookId <= 0) continue;

                // si ya existe para este usuario
                $stmt = $this->db->prepare('SELECT COUNT(*) AS cnt FROM purchases WHERE user_id = ? AND book_id = ?');
                $stmt->execute([$userId, $bookId]);
                $row = $stmt->fetch();
                if ($row && intval($row['cnt']) > 0) continue;

                // intentar encontrar registro anonimo existente
                $stmt2 = $this->db->prepare('SELECT id FROM purchases WHERE user_id IS NULL AND book_id = ? LIMIT 1');
                $stmt2->execute([$bookId]);
                $anon = $stmt2->fetch();
                if ($anon && !empty($anon['id'])) {
                    $upd = $this->db->prepare('UPDATE purchases SET user_id = ? WHERE id = ?');
                    $upd->execute([$userId, $anon['id']]);
                    $migrated++;
                    continue;
                }

                // no existe nada: insertar una compra sin tocar stock (evitar decrementar doblemente)
                $ins = $this->db->prepare('INSERT INTO purchases (user_id, book_id) VALUES (?, ?)');
                $ins->execute([$userId, $bookId]);
                $migrated++;
            }
        } catch (\Exception $e) {
            // silent fail: devolver lo que se haya migrado hasta ahora
        }
        return $migrated;
    }

    // Crear nuevo libro. Devuelve id del libro creado o false.
    public function createBook(string $titulo, string $autor, string $genero, string $sinopsis, int $ejemplares, string $img): int|false
    {
        if (!$this->db || empty(trim($titulo))) return false;
        try {
            $ins = $this->db->prepare('INSERT INTO libros (titulo, autor, genero, sinopsis, ejemplares, img) VALUES (?, ?, ?, ?, ?, ?)');
            $ins->execute([
                trim($titulo),
                trim($autor),
                trim($genero),
                trim($sinopsis),
                max(0, intval($ejemplares)),
                trim($img)
            ]);
            return intval($this->db->lastInsertId());
        } catch (Exception $e) {
            return false;
        }
    }

    // Actualizar libro existente. Devuelve true/false.
    public function updateBook(int $id, string $titulo, string $autor, string $genero, string $sinopsis, int $ejemplares, string $img): bool
    {
        if (!$this->db || $id <= 0 || empty(trim($titulo))) return false;
        try {
            $upd = $this->db->prepare('UPDATE libros SET titulo=?, autor=?, genero=?, sinopsis=?, ejemplares=?, img=? WHERE id=?');
            $upd->execute([
                trim($titulo),
                trim($autor),
                trim($genero),
                trim($sinopsis),
                max(0, intval($ejemplares)),
                trim($img),
                $id
            ]);
            return $upd->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // Eliminar libro. Devuelve true/false.
    public function deleteBook(int $id): bool
    {
        if (!$this->db || $id <= 0) return false;
        try {
            $del = $this->db->prepare('DELETE FROM libros WHERE id = ?');
            $del->execute([$id]);
            return $del->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // Obtener datos de un usuario por id (incluye is_admin)
    public function getUserById(int $id)
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare('SELECT id, nombre, apellido, email, passwd, COALESCE(is_admin,0) AS is_admin FROM usuarios WHERE id = ? LIMIT 1');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Asegura la existencia de la columna is_admin en la tabla usuarios.
    // Devuelve ['ok' => true] si ya existe o se creó correctamente,
    // o ['ok' => false, 'msg' => 'error message', 'sql' => 'ALTER ...'] si falla.
    public function ensureIsAdminColumnExists(): array
    {
        if (!$this->db) return ['ok' => false, 'msg' => 'No DB connection'];
        $sql = "ALTER TABLE usuarios ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'usuarios' AND column_name = 'is_admin'");
            $stmt->execute();
            $r = $stmt->fetch();
            if ($r && intval($r['cnt']) > 0) {
                return ['ok' => true];
            }
            // Intentar crear la columna
            $this->db->exec($sql);
            // Marcar usuario id=1 como admin si existe
            try {
                $this->db->exec("UPDATE usuarios SET is_admin = 1 WHERE id = 1");
            } catch (\Exception $e) {
                // no crítico
            }
            // comprobar de nuevo
            $stmt->execute();
            $r2 = $stmt->fetch();
            if ($r2 && intval($r2['cnt']) > 0) return ['ok' => true];
            return ['ok' => false, 'msg' => 'No se pudo verificar la columna tras crearla.', 'sql' => $sql];
        } catch (\PDOException $e) {
            return ['ok' => false, 'msg' => $e->getMessage(), 'sql' => $sql];
        }
    }
}
?>
