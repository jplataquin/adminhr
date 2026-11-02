<?php

namespace App\Models\Traits;

trait NormalizesHtmlEntities
{
    /**
     * Boot the trait to normalize HTML entities across specified fields on saving.
     */
    protected static function bootNormalizesHtmlEntities(): void
    {
        static::saving(function ($model) {
            $fields = $model->normalizableFields ?? [];
            foreach ($fields as $field) {
                if (isset($model->attributes[$field]) && is_string($model->attributes[$field])) {
                    $model->attributes[$field] = static::normalizeHtmlEntities($model->attributes[$field]);
                }
            }
        });
    }

    /**
     * Decode multiple levels of HTML entities and unescaped entity forms.
     */
    public static function normalizeHtmlEntities(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $prev = $text;
        while (($decoded = html_entity_decode($prev, ENT_QUOTES | ENT_HTML5, 'UTF-8')) !== $prev) {
            $prev = $decoded;
        }

        // Also handle lone or malformed &amp without semicolon
        $prev = preg_replace('/&amp(?=[^a-zA-Z0-9#;]|$)/i', '&', $prev);

        return $prev;
    }
}
