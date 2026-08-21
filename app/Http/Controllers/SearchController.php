<?php

namespace App\Http\Controllers;

use App\Support\SiteSearch;
use Illuminate\Http\Request;

/**
 * Site-wide search: the results page and the autocomplete endpoint.
 */
class SearchController extends Controller
{
    public function __construct(private SiteSearch $search)
    {
    }

    /**
     * Full results page (/search?q=...).
     */
    public function index(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        $results = $this->search->search($term, 12);

        return view('search', [
            'term' => $term,
            'results' => $results,
            'minLength' => SiteSearch::MIN_LENGTH,
        ]);
    }

    /**
     * JSON autocomplete for the search bar (/search/suggestions?q=...).
     *
     * Flattened into a single ordered list with group headings so the
     * dropdown can render it directly and arrow keys walk it linearly.
     */
    public function suggestions(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        $results = $this->search->search($term, 4);

        $items = [];
        foreach ($results['groups'] as $key => $group) {
            $items[] = ['kind' => 'heading', 'label' => SiteSearch::groupLabel($key)];

            foreach ($group as $item) {
                $items[] = $item + ['kind' => 'item'];
            }
        }

        return response()->json([
            'query' => $term,
            'total' => $results['total'],
            'items' => $items,
            // Fallback when nothing in the list is the right answer
            'all_url' => route('search', ['q' => $term]),
        ]);
    }
}
