<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Solicitudes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Barra lateral -->
        <div class="d-flex">
            <nav class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
                <h4 class="text-center">Menú</h4>
                <ul class="nav flex-column">
                    <li class="nav-item"><a href="#" class="nav-link text-white">Dashboard</a></li>
                    <li class="nav-item"><a href="#" class="nav-link text-white">Solicitudes</a></li>
                    <li class="nav-item"><a href="#" class="nav-link text-white">Perfil</a></li>
                    <li class="nav-item"><a href="#" class="nav-link text-white">Foro Feedback</a></li>
                </ul>
            </nav>
            
            <!-- Contenido Principal -->
            <div class="container p-4" style="flex: 1;">
                <h2>Dashboard</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">Pendientes</div>
                            <div class="card-body">
                                <h5 class="card-title">12 Solicitudes</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-header">En Proceso</div>
                            <div class="card-body">
                                <h5 class="card-title">5 Solicitudes</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">Completadas</div>
                            <div class="card-body">
                                <h5 class="card-title">20 Solicitudes</h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla de Solicitudes -->
                <h3>Solicitudes Recientes</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Manager</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#001</td>
                            <td>Juan Pérez</td>
                            <td><span class="badge bg-warning">En Proceso</span></td>
                            <td>2025-02-25</td>
                        </tr>
                        <tr>
                            <td>#002</td>
                            <td>María Gómez</td>
                            <td><span class="badge bg-primary">Pendiente</span></td>
                            <td>2025-02-24</td>
                        </tr>
                        <tr>
                            <td>#003</td>
                            <td>Carlos Díaz</td>
                            <td><span class="badge bg-success">Completado</span></td>
                            <td>2025-02-23</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
