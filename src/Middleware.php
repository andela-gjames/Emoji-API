<?php

namespace BB8\Emoji;


use BB8\Emoji\Exceptions\TokenExpirationException;
use BB8\Emoji\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Middleware
{
    /**
     * Runs at application level, ensures response is set to JSON format.
     *
     * @param ServerRequestInterface ServerRequestInterface $request  PSR-7 standard for receiving client request
     * @param ResponseInterface      ResponseInterface      $response PSR-& standard for sending server response
     * @param function                                      $next     callback function for calling next method
     *
     * @return ResponseInterface HTTP response of client request
     */
    public function init($request, $response, $next)
    {
        //Set return Content-type to JSON
        $response = $response->withAddedHeader('Content-type', 'application/json');

        //Call  next route controller
        $response = $next($request, $response);

        return $response;
    }

    /**
     * Authenticates that the user is allowed to make call to the route.
     *
     * @param ServerRequestInterface ServerRequestInterface $request  PSR-7 standard for receiving client request
     * @param ResponseInterface      ResponseInterface      $response PSR-& standard for sending server response
     * @param function                                      $next     callback function for calling next method
     *
     * @return ResponseInterface HTTP response of client request
     */
    public function authorize(ServerRequestInterface $request, $response, $next)
    {
        if (empty($request->getHeader('Authorization'))) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['message' => 'Token not found']));

            return $response;
        }
        //Get token for accessing this route
        $token = $request->getHeader('Authorization')[0];

        try {
            //Decode token to get object of data
            $decodedToken = Auth::decodeToken($token);

            //Extract the user id from decoded token
            $uid = $decodedToken->data->uid;

            $user = User::find($uid);

            //Check if user exist with the user id
            if ($user != null) {
                if ($user->isTokenValid($decodedToken)) {
                    $response = $next($request, $response);
                }
            } else {
                $response = $response->withStatus(401);
                $response->getBody()->write(json_encode(['message' => 'User does not exist']));
            }
        } catch (TokenExpirationException $ex) {
            $response = $response->withStatus(401);
            $response->getBody()->write(json_encode(['message' => $ex->getMessage()]));
        } catch (\Exception $ex) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['message' => $ex->getMessage()]));
        }

        return $response;
    }
}
