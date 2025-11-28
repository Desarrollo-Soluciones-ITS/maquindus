<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SearchIndexer
{
  protected $connection;

  public function __construct()
  {
    $this->connection = DB::connection('search');
  }

  /**
   * Actualiza o inserta un registro en el índice de búsqueda.
   */
  public function update($model)
  {
    $content = $model->getSearchableContent();
    $name = $model->getSearchResultName();
    $description = $model->getSearchResultDescription();

    $normalizedContent = $this->normalizeText($content);

    $this->connection->table('search_index')->updateOrInsert(
      [
        'model_type' => get_class($model),
        'model_id' => (string) $model->getKey(),
      ],
      [
        'searchable_content' => $content,
        'searchable_content_normalized' => $normalizedContent,
        'result_name' => $name,
        'result_description' => $description,
        'created_at' => $model->created_at ?? now(),
        'updated_at' => now(),
      ]
    );
  }

  /**
   * Elimina un registro del índice de búsqueda.
   */
  public function delete($model)
  {
    $this->connection->table('search_index')
      ->where('model_type', get_class($model))
      ->where('model_id', (string) $model->getKey())
      ->delete();
  }

  /**
   * Realiza una búsqueda paginada.
   */
  public function search(string $query = '', array $models = [], int $perPage = 10, int $page = 1): array
  {
    $queryBuilder = $this->connection->table('search_index');

    if (!empty($models)) {
      $queryBuilder->whereIn('model_type', $models);
    }

    if (!empty($query)) {
      $normalizedQuery = $this->normalizeText($query);
      $queryBuilder->where(function ($q) use ($normalizedQuery, $query) {
        $q->where('searchable_content_normalized', 'LIKE', "%{$normalizedQuery}%")
          ->orWhere('result_name', 'LIKE', "%{$query}%");
      });
    }

    $total = $queryBuilder->count();
    $offset = ($page - 1) * $perPage;

    $results = $queryBuilder->select(['model_id', 'model_type', 'result_name', 'result_description', 'created_at', 'updated_at'])
      ->orderBy('updated_at', 'desc')
      ->limit($perPage)
      ->offset($offset)
      ->get()
      ->toArray();

    return [
      'data' => $results,
      'total' => $total,
      'per_page' => $perPage,
      'current_page' => $page,
      'last_page' => ceil($total / $perPage),
    ];
  }

  /**
   * Normaliza el texto para búsquedas (quita acentos, convierte a minúsculas).
   */
  protected function normalizeText(string $text): string
  {
    $text = strtolower(trim($text));
    $accents = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'à', 'è', 'ì', 'ò', 'ù', 'ä', 'ë', 'ï', 'ö', 'ü'];
    $noAccents = ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'];
    $text = str_replace($accents, $noAccents, $text);
    $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
  }
}