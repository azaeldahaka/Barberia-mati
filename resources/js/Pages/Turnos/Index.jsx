import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Index({ turnos }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">Agenda (Turnos)</h2>
                    <Link
                        href={route('turnos.create')}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                    >
                        + Nuevo Turno
                    </Link>
                </div>
            }
        >
            <Head title="Turnos" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        {turnos.length === 0 ? (
                            <p className="text-gray-500 text-center py-8">No hay turnos agendados todavía.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm text-left text-gray-500">
                                    <thead className="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3">Fecha y Hora</th>
                                            <th className="px-6 py-3">Cliente</th>
                                            <th className="px-6 py-3">Servicio</th>
                                            <th className="px-6 py-3">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {turnos.map((turno) => (
                                            <tr key={turno.id} className="bg-white border-b">
                                                <td className="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    {new Date(turno.fecha_hora_inicio).toLocaleString()}
                                                    {' - '}
                                                    {new Date(turno.fecha_hora_fin).toLocaleTimeString()}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {turno.cliente.first_name} {turno.cliente.last_name}
                                                </td>
                                                <td className="px-6 py-4">
                                                    {turno.item_catalogo.nombre}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                                        {turno.estado}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
