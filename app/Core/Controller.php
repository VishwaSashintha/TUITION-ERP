<?php

declare(strict_types=1);

namespace App\Core;

/**
 * TuitionPro ERP — Base Controller
 */
abstract class Controller
{
    /**
     * Render a view and echo the output.
     */
    protected function render(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        echo View::render($view, $data, $layout);
    }

    /**
     * Alias for render().
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        $this->render($view, $data, $layout);
    }

    /**
     * Send a JSON response.
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    /**
     * Redirect to another URL with optional flash message.
     */
    protected function redirect(string $url, ?string $flashKey = null, ?string $flashMessage = null): void
    {
        if ($flashKey !== null && $flashMessage !== null) {
            flash($flashKey, $flashMessage);
        }
        Response::redirect($url);
    }

    /**
     * Redirect back to the previous page with optional flash message and old input.
     */
    protected function back(?string $flashKey = null, ?string $flashMessage = null, array $oldInput = []): void
    {
        if (!empty($oldInput)) {
            session('__old_input', $oldInput);
        }
        if ($flashKey !== null && $flashMessage !== null) {
            flash($flashKey, $flashMessage);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? url();
        Response::redirect($referer);
    }

    /**
     * Basic server-side input validation helper.
     * Throws an exception or returns errors array.
     *
     * @param array<string,string> $rules e.g. ['name' => 'required|min:3', 'email' => 'required|email']
     * @param array<string,mixed> $data
     * @return array<string,string> Array of errors, empty if valid
     */
    protected function validate(array $rules, array $data): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $value    = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $param = null;
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                if ($rule === 'required' && (empty($value) && $value !== '0')) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                    break;
                }

                if (!empty($value)) {
                    if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = 'Please provide a valid email address.';
                        break;
                    }

                    if ($rule === 'numeric' && !is_numeric($value)) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a number.';
                        break;
                    }

                    if ($rule === 'min' && strlen((string) $value) < (int) $param) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$param} characters.";
                        break;
                    }

                    if ($rule === 'max' && strlen((string) $value) > (int) $param) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$param} characters.";
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    protected function getTenantId(): ?int
    {
        return current_tenant_id();
    }

    protected function getUserId(): ?int
    {
        return auth_id();
    }

    protected function getBranchId(): ?int
    {
        return current_branch_id();
    }
}

