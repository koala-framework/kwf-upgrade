

use Symfony\Component\HttpFoundation\Request;
if (isset($_SERVER['REQUEST_URI']) && (
    (substr($_SERVER['REQUEST_URI'], 0, 5) == '/api/' && substr($_SERVER['REQUEST_URI'], 0, 12) != '/api/uploads')
    || substr($_SERVER['REQUEST_URI'], 0, 13) == '/kwf/symfony/'
)) {
    $kernel = AppKernel::getInstance();
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
    exit;
}

