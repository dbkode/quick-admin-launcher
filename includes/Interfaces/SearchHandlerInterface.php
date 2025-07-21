<?php
/**
 * Search Handler Interface
 *
 * @package QuickAL
 * @subpackage Interfaces
 * @since 1.0.0
 */

namespace QUICKAL\Interfaces;

interface SearchHandlerInterface
{
    /**
     * Search for items based on the given term
     *
     * @param string $term Search term
     * @return array Array of search results
     */
    public function search(string $term): array;
} 