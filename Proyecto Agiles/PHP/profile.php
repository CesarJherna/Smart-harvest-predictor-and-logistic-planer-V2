<?php
include 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET': // Obtener perfil
    $id = $_GET['id'] ?? null;
    if (!$id) {
      echo json_encode(["success" => false, "message" => "Falta el ID"]);
      exit;
    }
    $res = $conn->query("SELECT user_id, nombre_completo, email, celular FROM usuarios WHERE id_usuario=$id");
    echo json_encode($res->fetch_assoc());
    break;

  case 'PATCH': // Actualizar perfil
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id_usuario'];
    $nombre = $data['nombre_completo'];
    $email = $data['email'];
    $celular = $data['celular'];

    $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo=?, email=?, celular=? WHERE id_usuario=?");
    $stmt->bind_param("sssi", $nombre, $email, $celular, $id);
    $ok = $stmt->execute();

    echo json_encode(["success" => $ok, "message" => $ok ? "Perfil actualizado" : "Error al actualizar"]);
    break;

  case 'POST': // Cambiar contraseña
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id_usuario'];
    $actual = $data['actual'];
    $nueva = $data['nueva'];

    $res = $conn->query("SELECT password FROM usuarios WHERE id_usuario=$id");
    $hash = $res->fetch_assoc()['password'];

    if (!password_verify($actual, $hash)) {
      echo json_encode(["success" => false, "message" => "Contraseña actual incorrecta"]);
      exit;
    }

    $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET password=? WHERE id_usuario=?");
    $stmt->bind_param("si", $nuevoHash, $id);
    $ok = $stmt->execute();

    echo json_encode(["success" => $ok, "message" => $ok ? "Contraseña actualizada" : "Error al actualizar"]);
    break;
}
?>
