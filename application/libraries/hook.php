<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
*	Observer Pattern, uses CI's core Extension/Hook class
*	Made public so as to use more than default system hooks
*
*   @link http://codeigniter.com/user_guide/general/hooks.html
*/
class Hook {

	private $CI;
	private $hooks;

	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->load->library('hooks');

		$this->hooks =& $this->CI->hooks->hooks;
	}

	/**
	* 	Call Hook, passing params. 
	* 	Hooks are found in application/hooks directory or in code
	* 	
	* 	@param string 	The (namespaced) hook to call
	* 	@param mixed 	The parameter(s) to pass into the class method called
	* 	@return bool 	TRUE if successful, FALSE if not
	*/
	public function call($hook, $params=FALSE) {
		//Parse all hooks to call
		$parsed_hooks = $this->_parse($hook);

		//If no parameter, call hook like normal
		if($params === FALSE) {
			foreach($parsed_hooks as $parsed_hook) {
				$this->_call($parsed_hook);
			}
			return TRUE;
		}

		//If a parameter(s) are passed
		$this->_setupParams($parsed_hooks, $params);
		foreach($parsed_hooks as $parsed_hook) {
			$this->_call($parsed_hook);
		}
		return TRUE;
	}

	/**
	*	Ensure all called hooks
	*	Best used in namespaced format, eg: library.functionailty.location
	*
	*	@param array 	The hooks to inject params into
	*	@param mixed 	The parameters to pass to called hooks
	*	@return bool 	If run or not
	*/
	protected function _setupParams($hooks, $params) {
		if(is_array($hooks)) {
			foreach($hooks as $hook) {
				if( isset($this->hooks[$hook]) && is_array($this->hooks[$hook]) ) {
					//Inject new parameter(s) into hook
					foreach($this->hooks[$hook] as &$data) {
						$data['params'] = $params;
					}
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	*	Parse namespaced hook in formation something.namespaced.like_this
	*	Best used as library.functionailty.location
	*
	*	@param string 	The hook to parse
	*	@return array 	Array of hooks to call
	*/
	protected function _parse($hook) {
		$pos = strripos($hook, '.');
		$data = array($hook);

		//If FALSE or ZERO
		if($pos == FALSE) {
			return $data;
		}

		//If FALSE or ZERO, break from while loop
		while($pos != FALSE) {
			$hook = substr($hook, 0, $pos);
			$data[] = $hook;
			$pos = strripos($hook, '.');
		}
		return $data;
	}

	/**
	* 	Actually call hook
	*	Function may be called multiple times from $this->call()
	*	Depending on name-spacing
	*
	*	@param string 	Hook to call
	*	@return bool 	TRUE if called, FALSE if not
	*/
	protected function _call($hook) {
		return $this->CI->hooks->_call_hook($hook);
	}

	/**
	* 	Register a hook on the fly, in code.
	* 	
	* 	@param array 	The hook data, in format:
	*					array(
	*						'class'    => 'SomeClass',
	*						'function' => 'SomeMethod',
	*						'filename' => 'SomeFile.php',
	*						'filepath' => 'hooks', //OPTIONAL
	*						'params'   => $anything //OPTIONAL
	*					)
	*
	* 	@return bool 	TRUE if successful, FALSE if not
	*/
	public function register($hook, $data) {
		//Validate
		if($hook === '') {
			return FALSE;
		}

		if( is_array($data) === TRUE &&
			isset($data['class']) === TRUE &&
			isset($data['function']) === TRUE &&
			isset($data['filename']) === TRUE ) {

				if(isset($data['filepath']) === FALSE) {
					$data['filepath'] = 'hooks';
				}
				if(isset($data['params']) === FALSE) {
					$data['params'] = NULL;
				}

				$this->hooks[$hook][] = $data;
				return TRUE;

		}

		return FALSE;
	}


}