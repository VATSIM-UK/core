<?php
class Phery_Utils {
	/**
	 * Initialize phery by scannning the target class for response methods.
	 * This eliminates the need for phery::instance->set( ... array of response methods ), for quick setup.
	 * 
	 * An data-remote method defined on the client side should have a corresponding response method 
	 * with the same name prefixed by "ph_". Usage example:
	 *   
	 * View:
	 * 		<button data-remote="testmethod" >Call ph_testmethod in target class</button>
	 * 			or equivalent
	 * 		<?php echo phery::link_to('Call ph_testmethod in target class', 'testmethod', array('tag' => 'button')); ?>
	 * 
	 * Controller:
	 * 		public function before() {
	 *			parent::before();
	 *			Phery_Utils::quickinit($this); // <-- This init function looks for response method "ph_testmethod" in the controller itself ($this)		
	 *		}
	 *
	 * 		public function ph_testmethod($data = NULL) {  // <-- "ph_testmethod" corresponding to client side "testmethod" 
	 *			return phery_response::factory()->alert('Hello from ph_testmethod!');
	 *		}		  
	 * 
	 * @param $target_class  where to look for methods that will be triggered by ajax calls 
	 * @param $phery_callbacks  
	 */
	static public function quickinit($target_class, $phery_callbacks = array()) {
		try {
			phery::instance()
			->config(array(
	        	'exceptions' => true, // Throw exceptions and return them in form of phery_exception, usually for debug purposes
	        	'unobstructive' => array('thisone')
			))
			->callback($phery_callbacks)
			->set(self::get_response_methods($target_class))
			->process()
			;
		} catch (phery_exception $exc){
			echo phery_response::factory()->alert($exc->getMessage());
			exit;
		}
	}

	static private function get_response_methods($class) {
		$cm = get_class_methods($class);
		$response_methods = array();
		foreach($cm as $method) {
			if (strpos($method, 'ph_') === 0) {
				$callback_method_name = substr($method, 3);
				$response_methods[$callback_method_name] = array($class, $method);
			}
		}
		return $response_methods;
	}
}