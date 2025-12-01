<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGECEL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome sin SRI para evitar bloqueos de integridad en navegadores estrictos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />
    <style>
        :root {
            --brand-blue: #0d6efd;
            --brand-dark: #0a3560;
            --input-bg: #eef2f7;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: url('https://administrativo.cel.org.pe/vendor/crudbooster/assets/slider-light-2.jpg') center center / cover no-repeat fixed;
            position: relative;
            color: #2b2b2b;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(120deg, rgba(10,53,96,0.18), rgba(255,255,255,0.82));
            backdrop-filter: blur(2px);
            z-index: 0;
        }
        .auth-wrapper {
            position: relative;
            z-index: 1;
            width: min(640px, 90vw);
            display: flex;
            justify-content: center;
        }
        .auth-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.20);
            padding: 40px 46px;
            width: 100%;
            max-width: 480px;
            margin: auto;
            border: 1px solid rgba(13,110,253,0.08);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 6px;
        }
        .brand {
            text-align: center;
            margin-bottom: 18px;
        }
        .brand img {
            width: 300px;
            max-width: 300px;
            min-width: 300px;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 10px 28px rgba(0,0,0,0.14);
            display: block;
            margin: 0 auto 14px auto;
        }
        .brand h1 {
            display: none;
        }
        .subtitle {
            font-size: 15px;
            color: #57657c;
            margin-bottom: 34px;
        }
        .form-label {
            font-weight: 600;
            color: #3b4a5a;
        }
        .input-icon {
            position: relative;
        }
        .input-icon input {
            padding-left: 44px;
            border-radius: 14px;
            border: 1px solid #dbe2ea;
            background: var(--input-bg);
            height: 52px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .input-icon input:focus {
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
            background: #fff;
        }
        .input-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7a8aa0;
            font-size: 15px;
        }
        .btn-login {
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #1076c8 0%, #0d6efd 100%);
            border: none;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 14px 32px rgba(13, 110, 253, 0.28);
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #0f6ab3 0%, #0c63d4 100%);
        }
        .support-link {
            font-size: 14px;
            color: #0d6efd;
            text-decoration: none;
        }
        .support-link:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 12px;
        }
        @media (max-width: 640px) {
            .auth-card {
                padding: 30px 26px;
            }
            .brand {
                flex-direction: column;
                align-items: flex-start;
            }
            .brand h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="brand">
                <img src="https://administrativo.cel.org.pe/uploads/2024-07/5262f729642eeef306a0bbc7aec41f1d.png" alt="Colegio de Economistas de Lima">
                <h1>COLEGIO DE<br>ECONOMISTAS DE LIMA</h1>
            </div>
            <p class="subtitle mb-4">Por favor, inicia sesión para comenzar</p>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div class="mb-1">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Correo</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Ingresa su correo" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Ingresa su contraseña" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-unlock-alt me-2"></i> Iniciar
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
