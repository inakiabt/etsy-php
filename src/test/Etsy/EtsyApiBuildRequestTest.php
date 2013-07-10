<?php
namespace Etsy;

class EtsyApiBuildRequestTest extends \PHPUnit_Framework_TestCase
{
	protected $api;

	public function setUp()
	{
		parent::setUp();

		$client = new Mocks\EtsyClientMock(null, null);
		$this->api = new EtsyApi($client);
	}

	public function testValidMethod()
	{
		$args = array();

		$result = $this->api->getMethodTable();
		$this->assertEquals($result, array('path' => '/', 'data' => array(), 'method' => 'GET'));
	}

    /**
     * @expectedException Exception
     */
  	public function testInvalidMethod()
	{
		$this->api->getInvalidMethod();
	}

  	public function testValidParams()
	{
		$args = array(
			'params' => array(
				'tag' => 'fashion'
			)
		);

		$result = $this->api->getCategory($args);
		$this->assertEquals($result, array(
			'path' => '/categories/fashion', 
			'data' => array(),
			'method' => 'GET'));
	}

    /**
     * @expectedException Exception
     */
  	public function testInvalidParams()
	{
		$this->api->getCategory(array(
			'params' => array(
				'invalid_tag' => 'fashion'
			)
		));
	}

  	public function testValidData()
	{
		$args = array(
			'data' => array(
				"quantity" => 123456,
				"title" => "string",
				"description" => "text",
				"price" => 12.3456,
				"materials" => array('wood'),
				"shipping_template_id" => 123456,
				"shop_section_id" => 123456,
				"image_ids" => array(1,2,3,4,5,6),
				"non_taxable" => false,
				"state" => "active",
				"processing_min" => 123456,
				"processing_max" => 123456,
				"category_id" => 123456,
				"tags" => array('fashion'),
				"who_made" => "collective",
				"is_supply" => true,
				"when_made" => "2010_2013",
				"recipient" => "men",
				"occasion" => "baptism",
				"style" => array('style')
			)
		);

		$result = $this->api->createListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings', 
			'data' => array(
					"quantity" => 123456,
					"title" => "string",
					"description" => "text",
					"price" => 12.3456,
					"materials[]" => array('wood'),
					"shipping_template_id" => 123456,
					"shop_section_id" => 123456,
					"image_ids[]" => array(1,2,3,4,5,6),
					"non_taxable" => false,
					"state" => "active",
					"processing_min" => 123456,
					"processing_max" => 123456,
					"category_id" => 123456,
					"tags[]" => array('fashion'),
					"who_made" => "collective",
					"is_supply" => true,
					"when_made" => "2010_2013",
					"recipient" => "men",
					"occasion" => "baptism",
					"style[]" => array('style')
			),
			'method' => 'POST'));
	}

  	public function testValidParamsAndData()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321
			),
			'data' => array(
				"quantity" => 123456,
				"title" => "string",
				"description" => "text",
				"price" => 12.3456,
			)
		);

		$result = $this->api->updateListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321', 
			'data' => $args['data'],
			'method' => 'PUT'));
	}

    /**
     * @expectedException Exception
     */
  	public function testInvalidData()
	{
		$this->api->getCategory(array(
			'data' => array(
				'invalid_data_param' => 'fashion'
			)
		));
	}
    
    /**
     * @expectedException Exception
     */
  	public function testInvalidDataType()
	{
		$this->api->getCategory(array(
			'data' => array(
				'is_supply' => 123456
			)
		));
	}
}