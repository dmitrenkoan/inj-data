import { useEffect, useRef, useState } from 'react';
import TextInput from './TextInput';

function settlementLabel(value) {
    if (!value) {
        return '';
    }

    return typeof value === 'string' ? value : (value.name ?? '');
}

/**
 * Predictive settlement picker backed by /settlements/search. `value` is
 * either a settlement object ({ id, name, oblast, raion, type }) or a plain
 * string for free-text legacy fields that have no settlement id.
 * `onChange(settlement, text)` fires on every keystroke (settlement is null
 * until a suggestion is picked) and again with the full settlement on select.
 */
export default function CityAutocomplete({ value, onChange, requireSelection = true, className = '' }) {
    const [query, setQuery] = useState(settlementLabel(value));
    const [open, setOpen] = useState(false);
    const [results, setResults] = useState([]);
    const containerRef = useRef(null);
    const debounceRef = useRef(null);

    useEffect(() => {
        setQuery(settlementLabel(value));
    }, [value]);

    useEffect(() => {
        function handleClickOutside(e) {
            if (containerRef.current && !containerRef.current.contains(e.target)) {
                setOpen(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);

        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => () => clearTimeout(debounceRef.current), []);

    const handleInput = (e) => {
        const text = e.target.value;
        setQuery(text);
        setOpen(true);
        onChange(null, text);

        clearTimeout(debounceRef.current);
        const trimmed = text.trim();

        if (trimmed.length === 0) {
            setResults([]);
            return;
        }

        debounceRef.current = setTimeout(() => {
            fetch(`/settlements/search?q=${encodeURIComponent(trimmed)}`, {
                headers: { Accept: 'application/json' },
            })
                .then((res) => res.json())
                .then(setResults)
                .catch(() => setResults([]));
        }, 250);
    };

    const select = (s) => {
        setQuery(settlementLabel(s));
        setResults([]);
        setOpen(false);
        onChange(s, s.name);
    };

    const hasObjectValue = value && typeof value === 'object';

    return (
        <div className="relative" ref={containerRef}>
            <TextInput
                className={className}
                value={query}
                onChange={handleInput}
                onFocus={() => setOpen(true)}
                autoComplete="off"
                placeholder="Почніть вводити назву населеного пункту..."
            />

            {open && results.length > 0 && (
                <ul className="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                    {results.map((s) => (
                        <li key={s.id}>
                            <button
                                type="button"
                                className="block w-full px-3 py-1.5 text-left text-sm hover:bg-indigo-50"
                                onClick={() => select(s)}
                            >
                                <span className="block">
                                    {s.name}
                                    {s.type && <span className="text-gray-400"> ({s.type})</span>}
                                </span>
                                <span className="block text-xs text-gray-400">
                                    {[s.oblast, s.raion].filter(Boolean).join(', ')}
                                </span>
                            </button>
                        </li>
                    ))}
                </ul>
            )}

            {hasObjectValue && value.oblast && (
                <p className="mt-1 text-xs text-gray-500">
                    {[value.oblast, value.raion].filter(Boolean).join(', ')}
                </p>
            )}
            {requireSelection && query && !hasObjectValue && (
                <p className="mt-1 text-xs text-amber-600">Оберіть населений пункт зі списку підказок.</p>
            )}
        </div>
    );
}
