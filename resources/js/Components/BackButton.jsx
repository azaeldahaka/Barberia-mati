import { router } from '@inertiajs/react';

export default function BackButton({ fallback = '/', isDirty = false, className = '' }) {
    const handleBack = (e) => {
        e.preventDefault();

        if (isDirty) {
            if (!window.confirm("Tenés cambios sin guardar. ¿Estás seguro de que querés salir?")) {
                return;
            }
        }

        if (window.history.length > 2 || (window.history.length > 1 && document.referrer.includes(window.location.host))) {
            window.history.back();
        } else {
            router.visit(fallback);
        }
    };

    return (
        <button
            onClick={handleBack}
            className={`inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors ${className}`}
            type="button"
        >
            <svg className="mr-2 h-4 w-4" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </button>
    );
}
