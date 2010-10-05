<?php
/**
 * Axis
 * 
 * This file is part of Axis.
 * 
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Model_Form_Review extends Axis_Form
{
    protected $_translatorModule = 'community';
    
    /**
     * @param array $options[productId => int]
     */
    function __construct($options = null)
    {
        $product = $options['productId'];
        unset($options['productId']);
        $default = array(
            'id' => 'form-review',
            'action' => Zend_Controller_Front::getInstance()->getBaseUrl()
                . Axis_Locale::getLanguageUrl()
                . '/review/add/product/' . $product
        );
        if (is_array($options)) {
            $default = array_merge($default, $options);
        }
        
        parent::__construct($default);
        
        $this->addElement('hidden', 'product', array(
            'value' => $product
        ));
        $this->addElement('text', 'author', array(
            'required' => true,
            'label' => 'Nickname',
            'value' => Axis::single('community/common')->getNickname(),
            'class' => 'input-text required',
            'description' => 'Your nickname. All users will be able to see it',
            'validators' => array(
                new Axis_Community_Validate_Nickname()
            )
        ));
        $this->addElement('text', 'title', array(
            'required' => true,
            'label' => 'One-line summary',
            'class' => 'input-text required',
            'description' => 'Summarize your review in one line. up to 55 characters',
            'minlength' => '10',
            'maxlength' => '55',
            'validators' => array(
                new Zend_Validate_StringLength(10, 55, 'utf-8')
            )
        ));
        $this->addElement('textarea', 'pros', array(
            'required' => true,
            'label' => Axis::translate('community')->__('Pros'),
            'class' => 'input-text required',
            'description' => 'Tell us what you like about this product. up to 250 characters',
            'rows' => '2',
            'cols' => '50',
            'minlength' => '10',
            'maxlength' => '250',
            'validators' => array(
                new Zend_Validate_StringLength(10, 250, 'utf-8')
            )
        ));
        $this->addElement('textarea', 'cons', array(
            'required' => true,
            'label' => 'Cons',
            'class' => 'input-text required',
            'description' => "Tell us what you don't like about this product. up to 250 characters",
            'rows' => '2',
            'cols' => '50',
            'minlength' => '10',
            'maxlength' => '250',
            'validators' => array(
                new Zend_Validate_StringLength(10, 250, 'utf-8')
            )
        ));
        $this->addElement('textarea', 'summary', array(
            'label' => 'Summary',
            'class' => 'input-text',
            'description' => "Explain to us in detail why you like or dislike the product, focusing your comments on the product's features and functionality, and your experience using the product. This field is optional.",
            'rows' => '3',
            'cols' => '50',
            'minlength' => '10',
            'validators' => array(
                new Zend_Validate_StringLength(10, null, 'utf-8')
            )
        ));
        
        // ratings
        $ratings = array();
        if (!Axis::single('community/review_mark')->isCustomerVoted(Axis::getCustomerId(), $product)) {
            $marks = array('0.5' => 0.5, '1' => 1, '1.5' => 1.5, 2 => 2, '2.5' => 2.5, '3' => 3, '3.5' => 3.5, '4' => 4, '4.5' => 4.5, '5' => 5); //@todo make configurable
            foreach (Axis::single('community/review_rating')->getList() as $rating) {
                $this->addElement('select', 'rating_' . $rating['id'], array(
                    'required' => true,
                    'id' => $rating['name'],
                    //'name' => 'rating[' . $rating['id'] . ']', //Zend doesn't allow to do this
                    'label' => $rating['title'],
                    'class' => 'required review-rating'
                ));
                $this->getElement('rating_' . $rating['id'])
                    ->addMultiOptions($marks)
                    ->addDecorator('Label', array(
                        'tag' => '',
                        'class' => 'rating-title',
                        'placement' => 'prepend',
                        'separator' => ''
                    ))
                    ->setDisableTranslator(true);
                    
                $ratings[] = 'rating_' . $rating['id'];
            }
        }
        
        $this->addDisplayGroup(array_merge(
                $ratings, array('author', 'title', 'pros', 'cons', 'summary')
            ),
            'review'
        );
        
        if (count($ratings)) {
            $this->getDisplayGroup('review')
                ->addRow($ratings, 'row1');
        }
        
        $this->getDisplayGroup('review')
            ->addRow(array('author', 'title'), 'row2')
            ->addRow(array('pros', 'cons'), 'row3')
            ->addRow('summary', 'row4');
            
        $this->getDisplayGroup('review')->getRow('row2')
            ->addColumn('author', 'col1')
            ->addColumn('title', 'col2');
            
        $this->getDisplayGroup('review')->getRow('row3')
            ->addColumn('pros', 'col1')
            ->addColumn('cons', 'col2');
        
        if (Axis::single('community/review')->canAdd()) {
            $this->addElement('button', 'submit', array(
                'type' => 'submit',
                'class' => 'button',
                'label' => 'Add Review'
            ));
            
            $this->addActionBar(array('submit'));
        }
    }
}