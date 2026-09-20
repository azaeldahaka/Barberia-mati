import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="Bienvenido" />
            <div className="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-gray-900 px-4">
                <div className="max-w-3xl w-full text-center space-y-10">
                    <h1 className="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                        Sistema de Turnos de la Barbería
                    </h1>
                    
                    <p className="text-lg text-gray-600">
                        Por favor, seleccioná una de las opciones para continuar.
                    </p>

                    <div className="flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6 mt-8">
                        <Link
                            href={route('public.turno.create')}
                            className="w-full sm:w-auto rounded-lg bg-blue-600 px-8 py-4 text-white font-semibold text-lg hover:bg-blue-700 transition shadow-md"
                        >
                            Reservar Turno
                        </Link>
                        
                        {auth.user ? (
                            <Link
                                href={route('turnos.index')}
                                className="w-full sm:w-auto rounded-lg bg-gray-900 px-8 py-4 text-white font-semibold text-lg hover:bg-gray-800 transition shadow-md"
                            >
                                Ir a la Agenda
                            </Link>
                        ) : (
                            <Link
                                href={route('login')}
                                className="w-full sm:w-auto rounded-lg bg-gray-900 px-8 py-4 text-white font-semibold text-lg hover:bg-gray-800 transition shadow-md"
                            >
                                Acceso exclusivo personal
                            </Link>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
