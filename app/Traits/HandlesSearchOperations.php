<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Family;
use App\Models\Individual;
use App\Models\Media;
use App\Models\Repository;
use App\Models\Source;
use App\Models\Story;
use App\Models\Tree;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait for handling search operations
 */
trait HandlesSearchOperations
{
    /**
     * Search for individuals matching the given query.
     *
     * @param  string  $query  The search query
     * @param  int|null  $treeId  Optional tree ID to limit search
     * @param  int|null  $userId  Optional user ID to limit search
     * @param  array  $options  Additional search options
     * @return Builder Query builder with search results
     */
    protected function searchIndividuals(
        string $query,
        ?int $treeId = null,
        ?int $userId = null,
        array $options = []
    ): Builder {
        $builder = Individual::query();

        // Apply tree filter if provided
        if ($treeId !== null) {
            $builder->where('tree_id', $treeId);
        }

        // Apply user filter if provided
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }

        // Apply search conditions
        $builder->where(function ($q) use ($query) {
            $q->where('given_name', 'LIKE', "%{$query}%")
                ->orWhere('surname', 'LIKE', "%{$query}%")
                ->orWhere('nickname', 'LIKE', "%{$query}%")
                ->orWhere('birth_place', 'LIKE', "%{$query}%")
                ->orWhere('death_place', 'LIKE', "%{$query}%")
                ->orWhere('occupation', 'LIKE', "%{$query}%")
                ->orWhere('note', 'LIKE', "%{$query}%");

            // Search by full name (concatenated field)
            $q->orWhereRaw("CONCAT(given_name, ' ', surname) LIKE ?", ["%{$query}%"]);
        });

        // Apply additional filters from options
        if (isset($options['birth_year'])) {
            $builder->whereYear('birth_date', $options['birth_year']);
        }

        if (isset($options['death_year'])) {
            $builder->whereYear('death_date', $options['death_year']);
        }

        if (isset($options['sex'])) {
            $builder->where('sex', $options['sex']);
        }

        if (isset($options['living']) && $options['living'] === true) {
            $builder->whereNull('death_date');
        }

        if (isset($options['deceased']) && $options['deceased'] === true) {
            $builder->whereNotNull('death_date');
        }

        // Apply sorting
        $sortField = $options['sort_field'] ?? 'surname';
        $sortDirection = $options['sort_direction'] ?? 'asc';
        $builder->orderBy($sortField, $sortDirection);

        return $builder;
    }

    /**
     * Search for families matching the given query.
     *
     * @param  string  $query  The search query
     * @param  int|null  $treeId  Optional tree ID to limit search
     * @param  array  $options  Additional search options
     * @return Builder Query builder with search results
     */
    protected function searchFamilies(
        string $query,
        ?int $treeId = null,
        array $options = []
    ): Builder {
        $builder = Family::query();

        // Apply tree filter if provided
        if ($treeId !== null) {
            $builder->where('tree_id', $treeId);
        }

        // Apply search conditions
        $builder->where(function ($q) use ($query) {
            $q->where('marriage_place', 'LIKE', "%{$query}%")
                ->orWhere('marriage_type', 'LIKE', "%{$query}%")
                ->orWhere('divorce_place', 'LIKE', "%{$query}%");
        });

        // Search by husband or wife name
        $builder->orWhereHas('husband', function ($q) use ($query) {
            $q->where('given_name', 'LIKE', "%{$query}%")
                ->orWhere('surname', 'LIKE', "%{$query}%");
        });

        $builder->orWhereHas('wife', function ($q) use ($query) {
            $q->where('given_name', 'LIKE', "%{$query}%")
                ->orWhere('surname', 'LIKE', "%{$query}%");
        });

        // Apply additional filters from options
        if (isset($options['marriage_year'])) {
            $builder->whereYear('marriage_date', $options['marriage_year']);
        }

        if (isset($options['divorce_year'])) {
            $builder->whereYear('divorce_date', $options['divorce_year']);
        }

        if (isset($options['husband_id'])) {
            $builder->where('husband_id', $options['husband_id']);
        }

        if (isset($options['wife_id'])) {
            $builder->where('wife_id', $options['wife_id']);
        }

        if (isset($options['has_children']) && $options['has_children'] === true) {
            $builder->has('children');
        }

        // Apply sorting
        $sortField = $options['sort_field'] ?? 'id';
        $sortDirection = $options['sort_direction'] ?? 'asc';
        $builder->orderBy($sortField, $sortDirection);

        return $builder;
    }

    /**
     * Search for sources matching the given query.
     *
     * @param  string  $query  The search query
     * @param  int|null  $treeId  Optional tree ID to limit search
     * @param  int|null  $userId  Optional user ID to limit search
     * @param  array  $options  Additional search options
     * @return Builder Query builder with search results
     */
    protected function searchSources(
        string $query,
        ?int $treeId = null,
        ?int $userId = null,
        array $options = []
    ): Builder {
        $builder = Source::query();

        // Apply tree filter if provided
        if ($treeId !== null) {
            $builder->where('tree_id', $treeId);
        }

        // Apply user filter if provided
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }

        // Apply search conditions
        $builder->where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('author', 'LIKE', "%{$query}%")
                ->orWhere('publication', 'LIKE', "%{$query}%")
                ->orWhere('call_number', 'LIKE', "%{$query}%")
                ->orWhere('citation', 'LIKE', "%{$query}%");
        });

        // Search by repository name
        $builder->orWhereHas('repository', function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%");
        });

        // Apply additional filters from options
        if (isset($options['repository_id'])) {
            $builder->where('repository_id', $options['repository_id']);
        }

        if (isset($options['data_quality'])) {
            $builder->where('data_quality', $options['data_quality']);
        }

        // Apply sorting
        $sortField = $options['sort_field'] ?? 'title';
        $sortDirection = $options['sort_direction'] ?? 'asc';
        $builder->orderBy($sortField, $sortDirection);

        return $builder;
    }

    /**
     * Search for media matching the given query.
     *
     * @param  string  $query  The search query
     * @param  int|null  $treeId  Optional tree ID to limit search
     * @param  int|null  $userId  Optional user ID to limit search
     * @param  array  $options  Additional search options
     * @return Builder Query builder with search results
     */
    protected function searchMedia(
        string $query,
        ?int $treeId = null,
        ?int $userId = null,
        array $options = []
    ): Builder {
        $builder = Media::query();

        // Apply tree filter if provided
        if ($treeId !== null) {
            $builder->where('tree_id', $treeId);
        }

        // Apply user filter if provided
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }

        // Apply search conditions
        $builder->where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhere('file_name', 'LIKE', "%{$query}%")
                ->orWhere('file_type', 'LIKE', "%{$query}%");
        });

        // Apply additional filters from options
        if (isset($options['file_type'])) {
            $builder->where('file_type', $options['file_type']);
        }

        if (isset($options['individual_id'])) {
            $builder->whereHas('individuals', function ($q) use ($options) {
                $q->where('individual_id', $options['individual_id']);
            });
        }

        // Apply sorting
        $sortField = $options['sort_field'] ?? 'title';
        $sortDirection = $options['sort_direction'] ?? 'asc';
        $builder->orderBy($sortField, $sortDirection);

        return $builder;
    }

    /**
     * Search for stories matching the given query.
     *
     * @param  string  $query  The search query
     * @param  int|null  $treeId  Optional tree ID to limit search
     * @param  int|null  $userId  Optional user ID to limit search
     * @param  array  $options  Additional search options
     * @return Builder Query builder with search results
     */
    protected function searchStories(
        string $query,
        ?int $treeId = null,
        ?int $userId = null,
        array $options = []
    ): Builder {
        $builder = Story::query();

        // Apply tree filter if provided
        if ($treeId !== null) {
            $builder->where('tree_id', $treeId);
        }

        // Apply user filter if provided
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }

        // Apply search conditions
        $builder->where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%");
        });

        // Apply additional filters from options
        if (isset($options['individual_id'])) {
            $builder->whereHas('individuals', function ($q) use ($options) {
                $q->where('individual_id', $options['individual_id']);
            });
        }

        // Apply sorting
        $sortField = $options['sort_field'] ?? 'title';
        $sortDirection = $options['sort_direction'] ?? 'asc';
        $builder->orderBy($sortField, $sortDirection);

        return $builder;
    }

    /**
     * Search for trees matching the given query.
     *
     * @param  string  $query  The search query
     * @param  int|null  $userId  Optional user ID to limit search
     * @param  array  $options  Additional search options
     * @return Builder Query builder with search results
     */
    protected function searchTrees(
        string $query,
        ?int $userId = null,
        array $options = []
    ): Builder {
        $builder = Tree::query();

        // Apply user filter if provided
        if ($userId !== null) {
            $builder->where('user_id', $userId);
        }

        // Apply search conditions
        $builder->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%");
        });

        // Apply additional filters from options
        if (isset($options['is_public'])) {
            $builder->where('is_public', $options['is_public']);
        }

        if (isset($options['shared_with_user_id'])) {
            $builder->whereHas('sharedUsers', function ($q) use ($options) {
                $q->where('users.id', $options['shared_with_user_id']);
            });
        }

        // Apply sorting
        $sortField = $options['sort_field'] ?? 'name';
        $sortDirection = $options['sort_direction'] ?? 'asc';
        $builder->orderBy($sortField, $sortDirection);

        return $builder;
    }

    /**
     * Perform a global search across all entity types.
     *
     * @param  string  $query  The search query
     * @param  int|null  $treeId  Optional tree ID to limit search
     * @param  int|null  $userId  Optional user ID to limit search
     * @param  array  $options  Additional search options
     * @return array Array of search results by entity type
     */
    protected function globalSearch(
        string $query,
        ?int $treeId = null,
        ?int $userId = null,
        array $options = []
    ): array {
        $results = [];

        // Set limit for each entity type
        $limit = $options['limit'] ?? 10;

        // Search individuals
        $results['individuals'] = $this->searchIndividuals($query, $treeId, $userId, $options)
            ->limit($limit)
            ->get();

        // Search families
        $results['families'] = $this->searchFamilies($query, $treeId, $options)
            ->limit($limit)
            ->get();

        // Search sources
        $results['sources'] = $this->searchSources($query, $treeId, $userId, $options)
            ->limit($limit)
            ->get();

        // Search media
        $results['media'] = $this->searchMedia($query, $treeId, $userId, $options)
            ->limit($limit)
            ->get();

        // Search stories
        $results['stories'] = $this->searchStories($query, $treeId, $userId, $options)
            ->limit($limit)
            ->get();

        // Search trees (only if no tree ID is specified)
        if ($treeId === null) {
            $results['trees'] = $this->searchTrees($query, $userId, $options)
                ->limit($limit)
                ->get();
        }

        return $results;
    }

    /**
     * Format search results for API response.
     *
     * @param  array  $results  The search results by entity type
     * @return array Formatted search results
     */
    protected function formatSearchResults(array $results): array
    {
        $formatted = [];

        foreach ($results as $entityType => $entities) {
            $formatted[$entityType] = [];

            foreach ($entities as $entity) {
                $formatted[$entityType][] = $this->formatEntityForSearch($entity, $entityType);
            }
        }

        return $formatted;
    }

    /**
     * Format a single entity for search results.
     *
     * @param  Model  $entity  The entity to format
     * @param  string  $entityType  The type of entity
     * @return array Formatted entity data
     */
    protected function formatEntityForSearch(Model $entity, string $entityType): array
    {
        $data = [
            'id' => $entity->id,
            'type' => $entityType,
        ];

        switch ($entityType) {
            case 'individuals':
                $data['display_name'] = $entity->display_name;
                $data['birth_date'] = $entity->birth_date;
                $data['death_date'] = $entity->death_date;
                $data['sex'] = $entity->sex;
                break;

            case 'families':
                $data['husband'] = $entity->husband ? $entity->husband->display_name : null;
                $data['wife'] = $entity->wife ? $entity->wife->display_name : null;
                $data['marriage_date'] = $entity->marriage_date;
                $data['children_count'] = $entity->children->count();
                break;

            case 'sources':
                $data['title'] = $entity->title;
                $data['author'] = $entity->author;
                $data['repository'] = $entity->repository ? $entity->repository->name : null;
                break;

            case 'media':
                $data['title'] = $entity->title;
                $data['file_type'] = $entity->file_type;
                $data['thumbnail_url'] = $entity->thumbnail_url;
                break;

            case 'stories':
                $data['title'] = $entity->title;
                $data['excerpt'] = \Illuminate\Support\Str::limit($entity->content, 100);
                break;

            case 'trees':
                $data['name'] = $entity->name;
                $data['is_public'] = $entity->is_public;
                $data['individual_count'] = $entity->individuals()->count();
                break;

            default:
                // For unknown entity types, just return the entity attributes
                $data = array_merge($data, $entity->toArray());
                break;
        }

        return $data;
    }
}
