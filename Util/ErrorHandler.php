<?php
//Ihletés: https://github.com/yiisoft/yii2/blob/master/framework/base/ErrorHandler.php#L60

class ErrorHandler {
	
	/**
	 * Register this error handler
	 */
	public static function register(){
		ini_set('display_errors', false);
		register_shutdown_function( 'ErrorHandler::handleFatalError');
		set_exception_handler( 'ErrorHandler::handleException');
		set_error_handler('ErrorHandler::handleError');
		
	}
	
	/**
	 * Unregisters this error handler by restoring the PHP error and exception handlers.
	 */
	public static function unregister(){
		restore_error_handler();
		restore_exception_handler();
	}
	
	public static function handleError($severity, $message, $file, $line) {
		if (!(error_reporting() & $severity)) {
			// This error code is not included in error_reporting
			return;
		}
		throw new ErrorException($message, 0, $severity, $file, $line);
	}
	
	public static function handleFatalError(){
		
		$error = error_get_last();
		$errorType = (int)$error['type'];
		
	
		//if ($error['type'] === E_USER_ERROR) {
		
		//if (!empty($error['message'])){
			//Werror_log(getcwd());
		if (($errorType < 4097) && ($errorType > 0)){
			/*
			error_log($error['message']);
			error_log($error['type']);
			*/
			//spl_auto_load not working
			
			require_once 'Loader.php';
			require_once 'JsonParser.php';
			
			JsonParser::sendError(500, $error['message']);
			
		}
		
		
	}
	
/**
     * Handles uncaught PHP exceptions.
     *
     * This method is implemented as a PHP exception handler.
     *
     * @param \Exception $exception the exception that is not caught
     */
    public static function handleException($exception)
    {
    	self::unregister();
    	
        if ($exception instanceof ExitException) {
            return;
        }
        
        if (($exception instanceof InvalidArgumentException) != 1){
        	Logger::error($exception);
        }

		//error_reporting(0);
	    //ini_set('display_errors', false);

        if (function_exists('$exception->getName()')){
        	trigger_error("{$exception->getName()}: {$exception->getMessage()}", E_USER_ERROR);
        }
        else {
        	trigger_error($exception->getMessage(), E_USER_ERROR);
        }

    }	
	
}