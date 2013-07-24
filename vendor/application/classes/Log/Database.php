<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Database log writer, store system logs in a database group defined.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Anthony Lawrence
 * @copyright  (c) 2013
 * @license    http://kohanaframework.org/license
 */
class Log_Database extends Log_Writer {

	/**
	 * Creates a new file logger. 
	 *
	 *     $writer = new Log_File();
	 *
	 * @return  void
	 */
	public function __construct()
	{
		
	}

	/**
	 * Writes each of the messages to the database log table.
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   $messages
	 * @return  void
	 */
	public function write(array $messages)
	{
            foreach($messages as $message){
                $log = ORM::factory("Log_Database");
                foreach($message as $key=>$value){
                    $log->{$key} = $value;
                }
                $log->save();
            }
	}

} // End Kohana_Log_File