<?php
// ============================================================
// FarmersBD — Server-Side Validation
// ============================================================

class Validator {
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function required(string $field, string $label): static {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field] = "{$label} আবশ্যক।";
        }
        return $this;
    }

    public function min_length(string $field, int $min, string $label): static {
        if (isset($this->data[$field]) && mb_strlen(trim($this->data[$field])) < $min) {
            $this->errors[$field] = "{$label} কমপক্ষে {$min} অক্ষর হতে হবে।";
        }
        return $this;
    }

    public function max_length(string $field, int $max, string $label): static {
        if (isset($this->data[$field]) && mb_strlen(trim($this->data[$field])) > $max) {
            $this->errors[$field] = "{$label} সর্বোচ্চ {$max} অক্ষর হতে পারবে।";
        }
        return $this;
    }

    public function email(string $field, string $label = 'ইমেইল'): static {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "সঠিক {$label} ঠিকানা দিন।";
        }
        return $this;
    }

    public function mobile(string $field, string $label = 'মোবাইল নম্বর'): static {
        if (!empty($this->data[$field])) {
            $mobile = preg_replace('/\D/', '', $this->data[$field]);
            if (!preg_match('/^(01)[3-9]\d{8}$/', $mobile)) {
                $this->errors[$field] = "সঠিক {$label} দিন (01XXXXXXXXX)।";
            }
        }
        return $this;
    }

    public function numeric(string $field, string $label): static {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "{$label} অবশ্যই সংখ্যা হতে হবে।";
        }
        return $this;
    }

    public function positive(string $field, string $label): static {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && (float)$this->data[$field] <= 0) {
            $this->errors[$field] = "{$label} অবশ্যই শূন্যের চেয়ে বড় হতে হবে।";
        }
        return $this;
    }

    public function integer(string $field, string $label): static {
        if (!empty($this->data[$field]) && !ctype_digit((string)$this->data[$field])) {
            $this->errors[$field] = "{$label} অবশ্যই পূর্ণ সংখ্যা হতে হবে।";
        }
        return $this;
    }

    public function min(string $field, float $min, string $label): static {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && (float)$this->data[$field] < $min) {
            $this->errors[$field] = "{$label} কমপক্ষে {$min} হতে হবে।";
        }
        return $this;
    }

    public function max(string $field, float $max, string $label): static {
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && (float)$this->data[$field] > $max) {
            $this->errors[$field] = "{$label} সর্বোচ্চ {$max} হতে পারবে।";
        }
        return $this;
    }

    public function same(string $field, string $otherField, string $label): static {
        if (isset($this->data[$field], $this->data[$otherField]) 
            && $this->data[$field] !== $this->data[$otherField]) {
            $this->errors[$field] = "{$label} মিলছে না।";
        }
        return $this;
    }

    public function unique_in_db(string $field, string $table, string $column, ?int $excludeId = null, string $label = 'মান'): static {
        if (empty($this->errors[$field]) && !empty($this->data[$field])) {
            $sql    = "SELECT id FROM `{$table}` WHERE `{$column}` = ?";
            $params = [$this->data[$field]];
            if ($excludeId !== null) {
                $sql    .= " AND id != ?";
                $params[] = $excludeId;
            }
            if (db_query_one($sql, $params)) {
                $this->errors[$field] = "এই {$label} ইতিমধ্যে নিবন্ধিত।";
            }
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): static {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = "অবৈধ {$label} মান।";
        }
        return $this;
    }

    public function password_strength(string $field): static {
        $val = $this->data[$field] ?? '';
        if (empty($this->errors[$field]) && strlen($val) >= 8) {
            if (!preg_match('/[A-Z]/', $val) || !preg_match('/[0-9]/', $val)) {
                $this->errors[$field] = 'পাসওয়ার্ডে কমপক্ষে একটি বড় হাতের অক্ষর ও একটি সংখ্যা থাকতে হবে।';
            }
        }
        return $this;
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    public function first(string $field): string {
        return $this->errors[$field] ?? '';
    }

    public function error_html(string $field): string {
        if (!isset($this->errors[$field])) return '';
        return '<div class="invalid-feedback">' . e($this->errors[$field]) . '</div>';
    }

    public function has_error(string $field): bool {
        return isset($this->errors[$field]);
    }

    public function is_invalid(string $field): string {
        return $this->has_error($field) ? 'is-invalid' : '';
    }

    /**
     * Store errors in session for redirect + display pattern.
     */
    public function store_in_session(): void {
        $_SESSION['validation_errors'] = $this->errors;
        $_SESSION['old_input']         = $this->data;
    }
}

// ── Retrieve old input & errors from session ─────────────────

function old(string $field, $default = ''): string {
    $old = $_SESSION['old_input'][$field] ?? $default;
    unset($_SESSION['old_input'][$field]);
    return e((string) $old);
}

function validation_error(string $field): string {
    $errors = $_SESSION['validation_errors'] ?? [];
    if (!isset($errors[$field])) return '';
    $msg = $errors[$field];
    unset($_SESSION['validation_errors'][$field]);
    return '<div class="invalid-feedback d-block">' . e($msg) . '</div>';
}

function has_validation_error(string $field): bool {
    return isset($_SESSION['validation_errors'][$field]);
}

function is_invalid_class(string $field): string {
    return has_validation_error($field) ? 'is-invalid' : '';
}

/**
 * Clear session validation data (call after rendering the form).
 */
function clear_validation_session(): void {
    unset($_SESSION['validation_errors'], $_SESSION['old_input']);
}
