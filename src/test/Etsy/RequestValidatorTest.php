<?php
namespace Etsy;

class RequestValidatorTest extends \PHPUnit_Framework_TestCase
{
	protected $methods;
	protected $requiredParamRegExp = '@Required parameter ".+?" not found@';
	protected $requiredParametersRegExp = '@Required parameters not found: .+@';
	protected $unrecognizedDataRegExp = '@Unrecognized data param ".+?" \(.+?\)@';
	protected $invalidTypeRegExp = '@Invalid data param type ".+?" \(.+?\): required type ".+?"@';
	protected $invalidEnumTypeRegExp = '@Invalid enum data param ".+?" value \(.+?\): valid values ".+?"@';

	public function setUp()
	{
		parent::setUp();

		$methods_file = dirname(realpath(__FILE__)) . '/methods.json';

		if (!file_exists($methods_file))
		{
			exit("'{$methods}' not exists");
		}
		$this->methods = json_decode(file_get_contents($methods_file), true);
	}

	public function testValidMethod()
	{
		$method = 'getMethodTable';
		$args = array();

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
	}

	public function testMethodNotFound()
	{
		$method = 'invalidEtsyMethod';
		$args = array();

		$result = RequestValidator::validateParams($args, @$this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertEquals('Method not found', $result['_invalid'][0]);
	}

	public function testValidParameters()
	{
		// "uri": "/categories/:tag/:subtag/:subsubtag"
		$method = 'getSubSubCategory';
		$args = array(
			'params' => array(
				"tag" => "string",
				"subtag" => "string",
				"subsubtag" => "string"
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
	}

	public function testValidNotAllParameters()
	{
		// "uri": "/categories/:tag/:subtag/:subsubtag"
		$method = 'getSubSubCategory';
		$args = array(
			'params' => array(
				"subsubtag" => "string"
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(2, $result['_invalid']);
	}

	public function testValidNoParameters()
	{
		// "uri": "/countries"
		$method = 'findAllCountry';
		$args = array();

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
	}

	public function testInvalidNoParameters()
	{
		// "uri": "/countries"
		$method = 'getCategory';
		$args = array();

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->requiredParametersRegExp, $result['_invalid'][0]);
	}

	public function testParameterNotFound()
	{
		// "uri": "/categories/:tag"
		$method = 'getCategory';
		$args = array(
			'params' => array(
				'invalid_tag' => 'fashion'
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->requiredParamRegExp, $result['_invalid'][0]);
	}

	public function testDataEmpty()
	{
		/*
		"uri": "/listings"
	    "params": {
	      "quantity": "int",
	      "title": "string",
	      "description": "text",
	      "price": "float",
	      "materials": "array(string)",
	      "shipping_template_id": "int",
	      "shop_section_id": "int",
	      "image_ids": "array(int)",
	      "non_taxable": "boolean",
	      "image": "image",
	      "state": "enum(active, draft)",
	      "processing_min": "int",
	      "processing_max": "int",
	      "category_id": "int",
	      "tags": "array(string)",
	      "who_made": "enum(i_did, collective, someone_else)",
	      "is_supply": "boolean",
	      "when_made": "enum(made_to_order, 2010_2013, 2000_2009, 1994_1999, before_1994, 1990_1993, 1980s, 1970s, 1960s, 1950s, 1940s, 1930s, 1920s, 1910s, 1900s, 1800s, 1700s, before_1700)",
	      "recipient": "enum(men, women, unisex_adults, teen_boys, teen_girls, teens, boys, girls, children, baby_boys, baby_girls, babies, birds, cats, dogs, pets)",
	      "occasion": "enum(anniversary, baptism, bar_or_bat_mitzvah, birthday, canada_day, chinese_new_year, cinco_de_mayo, confirmation, christmas, day_of_the_dead, easter, eid, engagement, fathers_day, get_well, graduation, halloween, hanukkah, housewarming, kwanza, prom, july_4th, mothers_day, new_baby, new_years, quinceanera, retirement, st_patricks_day, sweet_16, sympathy, thanksgiving, valentines, wedding)",
	      "style": "array(string)"
	    },
		*/
		$method = 'createListing';
		$args = array(
			'data' => array(
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
	}

	public function testDataInvalidDataParam()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'invalid_data_param' => "any string"
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->unrecognizedDataRegExp, $result['_invalid'][0]);
	}

	public function testDataValidIntType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'quantity' => 123456
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals($args['data'], $result['_valid']);
	}

	public function testDataInvalidIntType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'quantity' => "any string"
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->invalidTypeRegExp, $result['_invalid'][0]);
	}

	public function testDataValidStringType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'title' => "any string"
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals($args['data'], $result['_valid']);
	}

	public function testDataInvalidStringType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'title' => 123456
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->invalidTypeRegExp, $result['_invalid'][0]);
	}

	public function testDataValidFloatType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'price' => 12.3456
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals($args['data'], $result['_valid']);
	}

	public function testDataInvalidFloatType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'price' => 123456
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->invalidTypeRegExp, $result['_invalid'][0]);
	}

	public function testDataValidEnumType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'who_made' => 'i_did'
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals($args['data'], $result['_valid']);
	}

	public function testDataInvalidEnumType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'who_made' => 'i_didnt'
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->invalidEnumTypeRegExp, $result['_invalid'][0]);
	}

	public function testDataValidBooleanType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'non_taxable' => true
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals($args['data'], $result['_valid']);
	}

	public function testDataInvalidBooleanType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'non_taxable' => 'any string'
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->invalidTypeRegExp, $result['_invalid'][0]);
	}

	public function testDataValidArrayType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'tags' => array('any', 'tag', 1)
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals($args['data'], $result['_valid']);
	}

	public function testDataValidEmptyArrayType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'tags' => array()
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals($args['data'], $result['_valid']);
	}

	public function testDataInvalidArrayType()
	{
		// "uri": "/listings"
		$method = 'createListing';
		$args = array(
			'data' => array(
				'tags' => 'any string'
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->invalidTypeRegExp, $result['_invalid'][0]);
	}

	public function testDataValidImageFileType()
	{
		/*
		    "uri": "/listings/:listing_id/images",
		    "params": {
		      "listing_id": "int",
		      "listing_image_id": "int",
		      "image": "imagefile",
		      "rank": "int",
		      "overwrite": "boolean"
		    },
		*/

		$method = 'uploadListingImage';
		$args = array(
			'params' => array(
				'listing_id' => 123456
			),
			'data' => array(
				'image' => array('@file.jpg;type=image/jpeg')
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayNotHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertEquals(array('@image' => '@file.jpg;type=image/jpeg'), $result['_valid']);
	}

	public function testDataInvalidImageFileType()
	{
		// "uri": "/listings/:listing_id/images"
		$method = 'uploadListingImage';
		$args = array(
			'params' => array(
				'listing_id' => 123456
			),
			'data' => array(
				'image' => array('file.jpg;type=image/jpeg')
			)
		);

		$result = RequestValidator::validateParams($args, $this->methods[$method]);
		$this->assertArrayHasKey('_invalid', $result, print_r(@$result['_invalid'], true));
		$this->assertCount(1, $result['_invalid']);
		$this->assertRegExp($this->invalidTypeRegExp, $result['_invalid'][0]);
	}
}