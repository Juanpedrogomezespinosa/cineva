<?php
require_once __DIR__ . '/db.php';

class Peliculas {

    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    /**
     * Obtener todas las películas
     */
    public function obtenerTodas() {
        $stmt = $this->pdo->query("SELECT * FROM peliculas ORDER BY fecha_agregado DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener películas de un usuario específico
     */
    public function obtenerPorUsuario($usuario_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM peliculas WHERE usuario_id = ? ORDER BY fecha_agregado DESC");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener película por ID
     */
    public function obtenerPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM peliculas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Agregar nueva película
     */
    public function agregar($usuario_id, $titulo, $genero, $plataforma, $portada = null) {
        $stmt = $this->pdo->prepare("INSERT INTO peliculas (usuario_id, titulo, genero, plataforma, portada) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$usuario_id, $titulo, $genero, $plataforma, $portada]);
    }

    /**
     * Obtener películas con filtros dinámicos
     * $filtros = [
     *     'genero' => 'Ciencia Ficción',
     *     'plataforma' => 'Netflix',
     *     'visto' => 1,
     *     'favorito' => 1
     * ]
     */
    public function obtenerConFiltros($filtros = []) {
        $query = "SELECT p.*, u.nombre AS usuario_nombre FROM peliculas p 
                  JOIN usuarios u ON p.usuario_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($filtros['genero'])) {
            $query .= " AND p.genero = ?";
            $params[] = $filtros['genero'];
        }

        if (!empty($filtros['plataforma'])) {
            $query .= " AND p.plataforma = ?";
            $params[] = $filtros['plataforma'];
        }

        if (isset($filtros['visto'])) {
            $query .= " AND p.visto = ?";
            $params[] = $filtros['visto'];
        }

        if (isset($filtros['favorito'])) {
            $query .= " AND p.favorito = ?";
            $params[] = $filtros['favorito'];
        }

        $query .= " ORDER BY p.fecha_agregado DESC";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
