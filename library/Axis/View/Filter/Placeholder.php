<?php

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Filter_Placeholder implements Zend_Filter_Interface
{
    /**
     *
     * @var Zend_View
     */
    protected $_view;

    public function setView($view)
    {
        $this->_view = $view;
    }
    
    /**
     * Injects additional scripts and styles, 
     * that was linked to headScript after it was outputed
     * This method allows to call scripts from Axis_Box
     * 
     * @param string $pageOutput
     */
    public function filter($pageOutput)
    {
        $head = substr($pageOutput, 0, strpos($pageOutput, '</head>'));
        
        if (empty($head)) {
            return $pageOutput;
        }
       
        $pageOutput = str_replace(
            array('{{headStyle}}', '{{headLink}}', '{{headScript}}'),
            array(
                $this->_view->headStyle()->toString(), 
                $this->_view->headLink()->toString(), 
                $this->_view->headScript()->toString()
            ), 
            $pageOutput
        );
        
        return $pageOutput;
    }
} 