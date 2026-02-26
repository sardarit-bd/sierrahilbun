// resources/js/hooks/useProductSearch.js
import { useState, useEffect, useRef, useCallback } from 'react';

export function useProductSearch(delay = 350) {
    const [query, setQuery]     = useState('');
    const [results, setResults] = useState([]);
    const [total, setTotal]     = useState(0);
    const [loading, setLoading] = useState(false);
    const [error, setError]     = useState(null);
    const abortRef              = useRef(null);

    const search = useCallback(async (q) => {
        if (abortRef.current) abortRef.current.abort();

        const trimmed = q.trim();

        if (trimmed.length < 2) {
            setResults([]);
            setTotal(0);
            setLoading(false);
            return;
        }

        abortRef.current = new AbortController();
        setLoading(true);
        setError(null);

        try {
            const res = await fetch(
                `/search/products?q=${encodeURIComponent(trimmed)}`,
                { signal: abortRef.current.signal }
            );

            if (!res.ok) throw new Error('Search failed');

            const data = await res.json();
            setResults(data.results);
            setTotal(data.total);
        } catch (err) {
            if (err.name !== 'AbortError') {
                setError('Something went wrong. Please try again.');
                setResults([]);
                setTotal(0);
            }
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        const timer = setTimeout(() => search(query), delay);
        return () => clearTimeout(timer);
    }, [query, delay, search]);

    const clear = useCallback(() => {
        if (abortRef.current) abortRef.current.abort();
        setQuery('');
        setResults([]);
        setTotal(0);
        setError(null);
        setLoading(false);
    }, []);

    return { query, setQuery, results, total, loading, error, clear };
}