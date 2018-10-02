<?php
namespace Etsy;

use PHPUnit\Framework\TestCase;

class EtsyApiBuildRequestTest extends TestCase
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
				"materials" => array('wood, plastic'),
				"shipping_template_id" => 123456,
				"shop_section_id" => 123456,
				"image_ids" => array(1), // Multimple?
				"non_taxable" => false,
				"state" => "active",
				"processing_min" => 123456,
				"processing_max" => 123456,
				"category_id" => 123456,
				"taxonomy_id" => 123456,
				"tags" => array('fashion, othertag'),
				"who_made" => "collective",
				"is_supply" => true,
				"when_made" => "2000_2009",
				"recipient" => "men",
				"occasion" => "baptism",
				"style" => array('style1, style2')
			)
		);

		$result = $this->api->createListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings',
			'data' => $args['data'],
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

	public function testValidJsonDataParam()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321
			),
			'data' => array(
				"variations" => array(
					'json' => json_encode(
			            array(
			              array(
			                'property_id' => 200,
			                'value' => "Black"
			              ),
			              array(
			                'property_id' => 200,
			                'value' => "White"
			              )
			            )
			        )
				)
			)
		);

		$result = $this->api->createListingVariations($args);
		$args['data']['variations'] = $args['data']['variations']['json'];
		$this->assertEquals($result, array(
			'path' => '/listings/654321/variations',
			'data' => $args['data'],
			'method' => 'POST'));
	}

	public function testValidMapParam()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321
			),
			'data' => array(
				"custom_property_names" => array(2, "Steel Black"),
				"variations" => array(
					'json' => json_encode(
			            array(
			              array(
			                'property_id' => 200,
			                'value' => "Black"
			              ),
			              array(
			                'property_id' => 200,
			                'value' => "White"
			              )
			            )
			        )
				)
			)
		);

		$result = $this->api->createListingVariations($args);
		$args['data']['variations'] = $args['data']['variations']['json'];
		$this->assertEquals($result, array(
			'path' => '/listings/654321/variations',
			'data' => $args['data'],
			'method' => 'POST'));
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

	public function testSimpleAssociations()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321
			),
			'associations' => array(
				'Images',
				'ShippingInfo'
			)
		);

		$result = $this->api->getListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321?includes=' . urlencode('Images,ShippingInfo'),
			'data' => array(),
			'method' => 'GET'));
	}

	public function testComposedAssociations()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321
			),
			'associations' => array(
				'ShippingInfo' => array(
					'scope' => 'active',
					'limit' => 1,
					'offset' => 0,
					'select' => array('currency_code', 'primary_cost')
				)
			)
		);

		$result = $this->api->getListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321?includes=' . urlencode('ShippingInfo(currency_code,primary_cost):active:1:0'),
			'data' => array(),
			'method' => 'GET'));
	}

	public function testComposedOptionalParamsAssociations()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321
			),
			'associations' => array(
				'ShippingInfo' => array(
					'limit' => 1,
					'offset' => 0
				)
			)
		);

		$result = $this->api->getListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321?includes=' . urlencode('ShippingInfo:1:0'),
			'data' => array(),
			'method' => 'GET'));
	}

	public function testComposedSubAssociations()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321
			),
			'associations' => array(
				'ShippingInfo' => array(
					'associations' => array(
						'DestinationCountry' => array(
							'select' => array('name', 'slug')
						)
					)
				)
			)
		);

		$result = $this->api->getListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321?includes=' . urlencode('ShippingInfo/DestinationCountry(name,slug)'),
			'data' => array(),
			'method' => 'GET'));
	}

	// Parameter Tests

	public function testComposedParameters()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321,
				'limit' => 10
			),
		);

		$result = $this->api->getListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321?limit=10',
			'data' => array(),
			'method' => 'GET'));
	}

	public function testComposedParametersWithAssociation()
	{
		$args = array(
			'params' => array(
				'listing_id' => 654321,
				'limit' => 10,
				'offset' => 20,
				'page' => 3
			),
			'associations' => array(
				'ShippingInfo' => array(
					'scope' => 'active',
					'limit' => 1,
					'offset' => 0,
					'select' => array('currency_code', 'primary_cost')
				)
			)
		);

		$result = $this->api->getListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321?limit=10&offset=20&page=3&includes=' . urlencode('ShippingInfo(currency_code,primary_cost):active:1:0'),
			'data' => array(),
			'method' => 'GET'));
	}

	// Fields Tests

	public function testComposedFields()
	{
		$args = array(
			'fields' => array(
				'listing_id',
				'title'
			)
		);

		$result = $this->api->findAllListingActive($args);
		$this->assertEquals($result, array(
			'path' => '/listings/active?fields=' . urlencode('listing_id,title'),
			'data' => array(),
			'method' => 'GET'));
	}

	public function testComposedFieldsWithAssociation()
	{
		$args = array(
			'fields' => array(
				'listing_id',
				'title'
			),
			'associations' => array(
				'ShippingInfo' => array(
					'scope' => 'active',
					'limit' => 1,
					'offset' => 0,
					'select' => array('currency_code', 'primary_cost')
				)
			)
		);

		$result = $this->api->findAllListingActive($args);
		$this->assertEquals($result, array(
			'path' => '/listings/active?includes=' . urlencode('ShippingInfo(currency_code,primary_cost):active:1:0') . '&fields=' . urlencode('listing_id,title'),
			'data' => array(),
			'method' => 'GET'));
	}

	public function testComposedFieldsWithAssociationAndParams()
	{
		$args = array(
			'fields' => array(
				'listing_id',
				'title'
			),
			'params' => array(
				'listing_id' => 654321,
				'limit' => 10,
				'offset' => 20,
				'page' => 3
			),
			'associations' => array(
				'ShippingInfo' => array(
					'scope' => 'active',
					'limit' => 1,
					'offset' => 0,
					'select' => array('currency_code', 'primary_cost')
				)
			)
		);

		$result = $this->api->getListing($args);
		$this->assertEquals($result, array(
			'path' => '/listings/654321?limit=10&offset=20&page=3&includes=' . urlencode('ShippingInfo(currency_code,primary_cost):active:1:0') . '&fields=' . urlencode('listing_id,title'),
			'data' => array(),
			'method' => 'GET'));
	}

}
