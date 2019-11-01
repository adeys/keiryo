<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 02/02/2019
 * Time: 08:08
 */

namespace Keiryo\Pagination;

use Keiryo\Database\Query\Builder;

class Paginator
{

    /**
     * @var array
     */
    private $items;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $current;

    /**
     * @var int
     */
    private $last;

    /**
     * @var string
     */
    private $url;

    /**
     * Paginate provided items
     *
     * @param Builder|array $items
     * @param int $page
     * @param int $perPage
     * @return Paginator
     * @throws \Throwable
     */
    public function paginate($items, int $page = 1, int $perPage = 10): Paginator
    {
        $this->current = $page = $page < 1 ? 1 : $page;

        if ($items instanceof Builder) {
            $this->total = (clone $items)->count();

            $this->last = ceil($this->total / $perPage);

            $page = abs($page - 1) * $perPage;
            $this->items = $items->limit($perPage)
                ->offset($page)
                ->get();
        } elseif (is_array($items)) {
            $this->total = count($items);

            $this->last = ceil($this->total / $perPage);

            $page = abs($page - 1) * $perPage;
            $this->items = array_slice($items, $page, $perPage);
        }

        $this->last = $this->last < 1 ? 1 : $this->last;

        return $this;
    }

    /**
     * Gets paginated items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Gets current page
     *
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * Set url for url generation
     *
     * @param string $url
     * @return Paginator
     */
    public function withUrl(string $url): Paginator
    {
        $this->url = rtrim($url, '/');
        return $this;
    }

    /**
     * First page url
     *
     * @return string|null
     */
    public function firstUrl(): ?string
    {
        return $this->urlFor($this->firstPage());
    }

    /**
     * @param int|null $page
     * @return string|null
     */
    private function urlFor(?int $page): ?string
    {
        if ($this->url) {
            return "{$this->url}?page=$page";
        }

        return null;
    }

    /**
     * Gets first page number
     *
     * @return int
     */
    public function firstPage(): int
    {
        return 1;
    }

    /**
     * Last page url
     *
     * @return string|null
     */
    public function lastUrl(): ?string
    {
        return $this->urlFor($this->lastPage());
    }

    /**
     * Gets last page number
     *
     * @return int
     */
    public function lastPage(): int
    {
        return $this->last;
    }

    /**
     * Previous page url
     *
     * @return string|null
     */
    public function prevUrl(): ?string
    {
        return $this->urlFor($this->prevPage());
    }

    /**
     * Gets previous page
     *
     * @return int|null
     */
    public function prevPage(): ?int
    {
        if (!$this->isFirst()) {
            return $this->current - 1;
        }

        return null;
    }

    /**
     * Checks wether it's the first page
     *
     * @return bool
     */
    public function isFirst(): bool
    {
        return 1 === $this->current;
    }

    /**
     * Next page url
     *
     * @return string|null
     */
    public function nextUrl(): ?string
    {
        return $this->urlFor($this->nextPage());
    }

    /**
     * Gets next page
     *
     * @return int|null
     */
    public function nextPage(): ?int
    {
        if ($this->hasMore()) {
            return $this->current + 1;
        }

        return null;
    }

    /**
     * Checks if paginator has more pages to show
     *
     * @return bool
     */
    public function hasMore(): bool
    {
        return $this->last > $this->current;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->total;
    }
}
