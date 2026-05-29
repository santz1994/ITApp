import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import { fetchVehicles } from '../../store/slices/vehicleSlice';

function VehicleList() {
    const dispatch = useDispatch();
    const { vehicles, loading } = useSelector((state) => state.vehicles);

    useEffect(() => {
        dispatch(fetchVehicles());
    }, [dispatch]);

    const statusColor = { available: 'green', in_use: 'blue', maintenance: 'yellow', retired: 'gray' };

    return (
        <div className="container-fluid">
            <div className="box box-primary">
                <div className="box-header with-border">
                    <h3 className="box-title"><i className="fa fa-car"></i> Manajemen Kendaraan</h3>
                    <div className="box-tools pull-right">
                        <Link to="/vehicles/create" className="btn btn-success btn-sm">
                            <i className="fa fa-plus"></i> Tambah Kendaraan
                        </Link>
                    </div>
                </div>
                <div className="box-body">
                    {loading ? (
                        <div className="text-center"><i className="fa fa-spinner fa-spin fa-3x"></i></div>
                    ) : (
                        <div className="row">
                            {vehicles.map((vehicle) => (
                                <div key={vehicle.id} className="col-md-4 col-sm-6">
                                    <div className="box box-widget">
                                        <div className="box-header with-border">
                                            <span className={`bg-${statusColor[vehicle.status] || 'gray'} label`}>
                                                {vehicle.status.replace('_', ' ').toUpperCase()}
                                            </span>
                                            <span style={{ marginLeft: 8, fontWeight: 'bold' }}>{vehicle.name}</span>
                                            <span className="description" style={{ display: 'block' }}>{vehicle.brand} {vehicle.model}</span>
                                        </div>
                                        <div className="box-body">
                                            <p><strong>Plat:</strong> {vehicle.plate_number}</p>
                                            <p><strong>Kapasitas:</strong> {vehicle.capacity} orang</p>
                                            <p><strong>KM:</strong> {Number(vehicle.current_mileage).toLocaleString()} km</p>
                                        </div>
                                        <div className="box-footer">
                                            <Link to={`/vehicles/${vehicle.id}`} className="btn btn-default btn-sm">
                                                <i className="fa fa-eye"></i> Detail
                                            </Link>
                                            {vehicle.status === 'available' && (
                                                <Link to={`/vehicle-bookings/create?vehicle_id=${vehicle.id}`} className="btn btn-success btn-sm pull-right">
                                                    <i className="fa fa-car"></i> Booking
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                            {vehicles.length === 0 && (
                                <div className="col-md-12 text-center" style={{ padding: 40 }}>
                                    <i className="fa fa-car fa-3x text-muted"></i>
                                    <p className="text-muted">Belum ada kendaraan terdaftar.</p>
                                    <Link to="/vehicles/create" className="btn btn-success"><i className="fa fa-plus"></i> Tambah Kendaraan</Link>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default VehicleList;