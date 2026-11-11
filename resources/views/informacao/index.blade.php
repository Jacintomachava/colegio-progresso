<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Título de la página</title>
    <!-- Enlaces a CSS, favicon, metadatos, etc. -->
</head>
<body>
    <!-- Contenido visible de la página -->
    <header>
        <h1>Informacao da Escola Lhaysso</h1>
        <nav>
            <!-- Menú de navegación -->
        </nav>
    </header>
    
    <main>
        <section>
          @foreach($informacoes as $informacao)
            <article>
                <h3>{{$informacao->titulo}}</h3>
                <p>{{$informacao->descricao}}</p>
            </article>
          @endforeach
        </section>
        
        <aside>
            <!-- Contenido secundario -->
        </aside>
    </main>
    
    <footer>
        <!-- Pie de página -->
    </footer>
    
    <!-- Scripts JavaScript -->
</body>
</html>