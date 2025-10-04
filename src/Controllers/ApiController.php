<?php

declare(strict_types=1);

namespace App\Controllers;

abstract class ApiController
{
    protected function sendJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    protected function sendSuccess(array $data = [], string $message = '', int $statusCode = 200): void
    {
        $response = ['success' => true];
        if (!empty($data)) {
            $response['data'] = $data;
        }
        if (!empty($message)) {
            $response['message'] = $message;
        }
        $this->sendJson($response, $statusCode);
    }

    protected function sendError(string $message, int $statusCode = 400, array $details = []): void
    {
        $response = ['success' => false, 'error' => $message];
        if (!empty($details)) {
            $response['details'] = $details;
        }
        $this->sendJson($response, $statusCode);
    }

    protected function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        
        // Si l'input est vide, essayer de récupérer depuis $_POST
        if (empty($input)) {
            return $_POST;
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError('Données JSON invalides: ' . json_last_error_msg());
        }
        
        return $data ?? [];
    }

    protected function validateRequired(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->sendError("Le champ '{$field}' est requis");
                exit; // Arrêter l'exécution
            }
        }
    }

    protected function validateEmail(?string $email): bool
    {
        if ($email === null || $email === '') {
            return true; // Email optionnel
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateDate(?string $date): bool
    {
        if ($date === null || $date === '') {
            return true; // Date optionnelle
        }
        
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    protected function validateDateTime(?string $datetime): bool
    {
        if ($datetime === null || $datetime === '') {
            return true; // DateTime optionnel
        }
        
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $d && $d->format('Y-m-d H:i:s') === $datetime;
    }

    protected function sanitizeString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return trim((string) $value);
    }

    protected function sanitizeFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (float) $value;
    }

    protected function sanitizeInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value;
    }

    protected function getQueryParam(string $name, $default = null)
    {
        return $_GET[$name] ?? $default;
    }

    protected function getPathParam(string $name, $default = null)
    {
        // Cette méthode sera utilisée par le routeur pour passer les paramètres d'URL
        return $default;
    }
}
