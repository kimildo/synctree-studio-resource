<?php
    namespace middleware;

    use Slim\Http\Request;
    use Slim\Http\Response;
    use Psr\Container\ContainerInterface;

    use libraries\constant\ErrorConst;
    use libraries\util\CommonUtil;
    use libraries\util\RedisUtil;
    use libraries\constant\CommonConst;
    use libraries\log\LogMessage;



    class CheckLogin
    {
        private $ci;
        private $flash;
        private $redis;
        private $sessionExpiredResult;

        public function __construct(ContainerInterface $ci)
        {
            $this->ci = $ci;
            $this->redis = $this->ci->get('redis');
            $this->flash = $ci->get('flash');
            $this->sessionExpiredResult = [
                'result' => ErrorConst::SESSION_EXPIRED,
                'data' => [
                    'message' => 'Session Expirede.',
                ]
            ];
        }

        /**
         * @param Request  $request
         * @param Response $response
         * @param callable $next
         *
         * @return Response
         * @throws \Exception
         */
        public function __invoke(Request $request, Response $response, callable $next)
        {
            $message = 'You have to login';

            /**
             * 로그인 체크
             */
            if (empty($_SESSION['sess_user']) && empty($_SESSION['partner'])) {

                LogMessage::error($message);
                LogMessage::info('Redirect Login');

                // ajax일 경우 json으로 리턴
                if ($request->hasHeader('HTTP_X_REQUESTED_WITH')) {
                    return $response->withJson($this->sessionExpiredResult, ErrorConst::SUCCESS_CODE);
                }

                return $response->withRedirect('/auth/signin');
            }

            /**
             * 다른 브라우저에서 로그인한 경우 현 브라우저에서 로그아웃 (로컬/DEV 제외)
             *
             */
            if (APP_ENV === APP_ENV_PRODUCTION || APP_ENV === APP_ENV_STAGING) {
                if ( ! empty($_SESSION['sess_user'])) {
                    $curSessData = RedisUtil::getData($this->redis, $_SESSION['sess_user']['sess_userid']);
                    if (isset($curSessData['sess_id']) && $curSessData['sess_id'] !== session_id()) {
                        LogMessage::error($message);
                        LogMessage::info('Redirect Login - Dup');
                        if ($request->hasHeader('HTTP_X_REQUESTED_WITH')) {
                            return $response->withJson($this->sessionExpiredResult, ErrorConst::SUCCESS_CODE);
                        }
                        return $response->withRedirect('/auth/signout');
                    }
                }
            }

            $route = $request->getAttribute('route');
            $routeName = $route->getName();

            /**
             * 팀 아이디가 없이 로그인 했을 경우 즉, SuperUser 일 경우
             */
            if ($routeName !== 'admin' && empty($_SESSION['sess_user']['sess_user_info']['team_id']) && !isset($_SESSION['partner']['data'])) {
                LogMessage::info('Redirect Admin');
                return $response->withRedirect('/console/admin', ErrorConst::MOVE_CODE);
            }

            /*
             * call next middle ware or application
             */
            return $next($request, $response);
        }



    }