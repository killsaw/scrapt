<?php

/**
 * PropertyFilter.php
 * 
 * PHP version 5
 * 
 * All rights reserved
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * 3. The names of the authors may not be used to endorse or promote products
 *     derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @category  PropertyFilter
 * @package    Musras
 * @author     Steven Bredenberg <steven@killsaw.com>
 * @copyright 2010 Steven Bredenberg
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @link        http://github.com/killsaw/Musras
 */


/**
 * Filter-chain handling.
 * 
 * Allows for named filter chains which can be applied to input data.
 * 
 * @category  PropertyFilter
 * @package    Musras
 * @author     Steven Bredenberg <steven@killsaw.com>
 * @copyright 2010 Steven Bredenberg
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @link        http://github.com/killsaw/Musras
 */
class PropertyFilter
{

     /**
      * Property filters.
      * @var     array
      * @access protected
      * @static
      */
    static protected $filters = array();
    
     /**
      * If passed to register(), will add filters onto an 
      * existing filter chain.
      */
    const APPEND_FILTERS = -1;
    
     /**
      * Register filter for provided name.
      * 
      * Supports closures, simple function strings, and string filter
      * chains of the format: call1|call2|call3, with piping between them.
      * String chains also may include call arguments, like so:
      *     'call1|call2("str", %, 23.4)'
      * The '%' character here is a stand-in for the calls input value,
      * whatever type that may be.
      * 
      * @param  string $property Name to assign to filter.
      * @param  mixed  $filter    Callable filter.
      * @param  mixed  $options  Optional: PropertyFilter::APPEND_FILTER
      *                                  adds onto an existing chain rather than recreating.
      * @return void      
      * @access public    
      * @throws Exception
      * @static
      */
    public static function register($property, $filter, $options=null)
    {
        if (!is_callable($filter)) {
            if (preg_match('/[^a-zA-Z_0-9:]/', $filter)) {
                $filter = self::filterfromString($filter);
            } else {
                throw new Exception("'$filter' is not a recognized filter.");
            }
        }
        
        if (isset(self::$filters[$property]) && $options == self::APPEND_FILTERS) {
            self::$filters[$property][] = $filter;
        } else {
            self::$filters[$property] = (array)$filter;
        }
    }
    
     /**
      * Applies registered filters to an associative array.
      * 
      * @param  array  $arr Array to make changes to.
      * @return array  Modified array.
      * @access public
      * @static
      */
    public static function filterArray($arr)
    {
        foreach ($arr as $k=>$v) {
            if (is_array($v)) {
                $arr[$k] = self::filterArray($v);
            } else {
                $arr[$k] = self::filter($k, $v);
            }
        }
        return $arr;
    }
    
     /**
      * Run registered filters for a property on a value.
      * 
      * @param  string $property Registered name for filter.
      * @param  mixed $value Value to modify.
      * @return mixed Modified value.
      * @access public 
      * @static
      */
    public static function filter($property, $value)
    {
        if (isset(self::$filters[$property])) {
            foreach((array)self::$filters[$property] as $func) {
                $value = call_user_func($func, $value);
            }
        }
        return $value;
    }
    
     /**
      * Builds a filter-chain from a string definition.
      * 
      * @param  string $string Filter-chain definition.
      * @return closure Callable filter chain.
      * @access protected
      * @static
      */
    protected static function filterfromString($string)
    {
        $calls = explode('|', $string);
        
        // Do an initial scan to make sure nothing contains params.
        foreach($calls as &$call) {
            if (strpos($call, '(') !== false) {
                $cmd = self::parseCommandCall($call);
                $call = function($val) use ($cmd) {
                    $args = $cmd['args'];

                    foreach($args as $k=>$a) {
                        if ($a == '%') {
                            $args[$k] = $val;
                        }
                    }
                    return call_user_func_array($cmd['call'], $args);
                };
            }
        }
        
        // Build our closure to run commands.
        $filter = function($val) use ($calls) {
            foreach((array)$calls as $c) {
                if (!is_callable($c)) {
                    if (is_callable('filter_'.$c)) {
                        $c = 'filter_'.$c;
                    } else {
                        continue;
                    }
                }
                $val = call_user_func($c, $val);
            }
            return $val;
        };
        return $filter;
    }
    
     /**
      * Parsing code for a filter chain segment. Uses PHP's tokenizer.
      * 
      * @param string $cmd_str Filter chain segment string.
      * @return array Call profile - name and args.
      * @access protected
      */
    protected function parseCommandCall($cmd_str)
    {
        $tokens = token_get_all('<'.'?php '.$cmd_str);
        $arg_start = false;
        $arg_list  = array();
        $call_name = '';
        
        foreach($tokens as $t) {
            if (!is_array($t)) {
                $t = array(0, $t);
            }
            list($id, $text) = $t;

            if ($id == T_WHITESPACE) {
                continue;
            }
            
            // Find start of function call.
            if ($text == '(' && !$arg_start) {
                $call_name = $last_text;
                $arg_start = true;
                continue;
            }
            
            // Find params for function call.
            if ($arg_start) {
                // Not supporting embedded commands.
                if (($text == ',' && $id == 0) || $text == ')') {
                    if ($last_id == T_CONSTANT_ENCAPSED_STRING) {
                        // Strip off quotes.
                        $last_text = substr($last_text, 1, -1);
                    } 
                    $arg_list[] = $last_text;
                }
                if ($text == ')') {
                    break;
                }
            }
            $last_text = $text;
            $last_id = $id;
        }
        return array('call'=>$call_name, 'args'=>$arg_list);
    }
}
