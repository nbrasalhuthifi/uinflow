<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public static function login(): void
    {
        if (current_user()) redirect('/');
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                verify_csrf();
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                $password = (string) ($_POST['password'] ?? '');
                $key = 'login_attempts_' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $email);
                $attempt = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];
                if (($attempt['until'] ?? 0) > time()) throw new InvalidArgumentException('تم إيقاف المحاولات مؤقتًا. حاول بعد قليل.');
                $statement = Database::connection()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
                $statement->execute([$email]);
                $user = $statement->fetch();
                if (!$user || !password_verify($password, $user['password'])) {
                    $attempt['count'] = ($attempt['count'] ?? 0) + 1;
                    if ($attempt['count'] >= 5) { $attempt['until'] = time() + 300; $attempt['count'] = 0; }
                    $_SESSION[$key] = $attempt;
                    throw new InvalidArgumentException('البريد الإلكتروني أو كلمة المرور غير صحيحة.');
                }
                unset($_SESSION[$key]);
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $u = Database::connection()->prepare('UPDATE users SET password = ?, last_login_at = NOW() WHERE id = ?');
                    $u->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
                } else {
                    Database::connection()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
                }
                unset($user['password']);
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                audit('LOGIN', 'users', (int) $user['id']);
                redirect('/');
            } catch (Throwable $exception) {
                $error = $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'تعذر تسجيل الدخول. تأكد من إعداد قاعدة البيانات.';
            }
        }
        (new self())->view('auth/login', ['error' => $error]);
    }

    public static function logout(): void
    {
        post_only();
        verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], '', (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        redirect('/?page=login');
    }
}
