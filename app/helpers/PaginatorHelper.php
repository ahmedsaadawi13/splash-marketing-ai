<?php
// FILE: /app/helpers/PaginatorHelper.php

class PaginatorHelper {
    private $total;
    private $perPage;
    private $currentPage;
    private $totalPages;
    private $baseUrl;

    public function __construct($total, $perPage = 20, $currentPage = 1, $baseUrl = '') {
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = ceil($total / $perPage);
        $this->baseUrl = $baseUrl;
    }

    public function getOffset() {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function getLimit() {
        return $this->perPage;
    }

    public function hasPages() {
        return $this->totalPages > 1;
    }

    public function hasPrevious() {
        return $this->currentPage > 1;
    }

    public function hasNext() {
        return $this->currentPage < $this->totalPages;
    }

    public function previousPage() {
        return max(1, $this->currentPage - 1);
    }

    public function nextPage() {
        return min($this->totalPages, $this->currentPage + 1);
    }

    public function getPages($range = 5) {
        $pages = [];
        $start = max(1, $this->currentPage - floor($range / 2));
        $end = min($this->totalPages, $start + $range - 1);

        if ($end - $start < $range - 1) {
            $start = max(1, $end - $range + 1);
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }

    public function render() {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<div class="pagination">';

        // Previous button
        if ($this->hasPrevious()) {
            $url = $this->buildUrl($this->previousPage());
            $html .= '<a href="' . htmlspecialchars($url) . '" class="pagination-link">&laquo; Previous</a>';
        } else {
            $html .= '<span class="pagination-link disabled">&laquo; Previous</span>';
        }

        // Page numbers
        foreach ($this->getPages() as $page) {
            $url = $this->buildUrl($page);
            if ($page == $this->currentPage) {
                $html .= '<span class="pagination-link active">' . $page . '</span>';
            } else {
                $html .= '<a href="' . htmlspecialchars($url) . '" class="pagination-link">' . $page . '</a>';
            }
        }

        // Next button
        if ($this->hasNext()) {
            $url = $this->buildUrl($this->nextPage());
            $html .= '<a href="' . htmlspecialchars($url) . '" class="pagination-link">Next &raquo;</a>';
        } else {
            $html .= '<span class="pagination-link disabled">Next &raquo;</span>';
        }

        $html .= '</div>';
        $html .= '<div class="pagination-info">Showing ' . (($this->currentPage - 1) * $this->perPage + 1) . ' to ' . min($this->currentPage * $this->perPage, $this->total) . ' of ' . $this->total . ' results</div>';

        return $html;
    }

    private function buildUrl($page) {
        $separator = strpos($this->baseUrl, '?') !== false ? '&' : '?';
        return $this->baseUrl . $separator . 'page=' . $page;
    }
}
