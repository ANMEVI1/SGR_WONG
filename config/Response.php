<?php
/**
 * Clase Response - Manejo estandarizado de respuestas JSON
 */
class Response {
    
    /**
     * Envía una respuesta JSON exitosa
     */
    public static function success(array $data = [], string $message = 'Operación exitosa'): void {
        self::sendJson([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * Envía una respuesta JSON de error
     */
    public static function error(string $message = 'Error interno', int $code = 500): void {
        http_response_code($code);
        self::sendJson([
            'success' => false,
            'message' => $message,
            'data' => null
        ]);
    }
    
    /**
     * Envía una respuesta JSON de validación
     */
    public static function validation(array $errors): void {
        http_response_code(422);
        self::sendJson([
            'success' => false,
            'message' => 'Errores de validación',
            'errors' => $errors
        ]);
    }
    
    /**
     * Envía respuesta JSON no autorizada
     */
    public static function unauthorized(string $message = 'No autorizado'): void {
        http_response_code(401);
        self::sendJson([
            'success' => false,
            'message' => $message,
            'data' => null
        ]);
    }
    
    /**
     * Envía respuesta JSON no encontrado
     */
    public static function notFound(string $message = 'Recurso no encontrado'): void {
        http_response_code(404);
        self::sendJson([
            'success' => false,
            'message' => $message,
            'data' => null
        ]);
    }
    
    /**
     * Método privado para enviar JSON
     */
    private static function sendJson(array $data): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}