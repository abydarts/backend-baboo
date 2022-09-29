<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Do_migrate extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->dbforge();
    }

    function index(){
      ## Create Table area_services
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'city_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'description' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("area_services", TRUE);
  		$this->db->query('ALTER TABLE  `area_services` ENGINE = InnoDB');

  		## Create Table auth_otp
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'user' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'activity' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => FALSE,
  				'default' => 'auth-otp',

  			),
  			'otp' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 10,
  				'null' => TRUE,

  			),
  			'channel' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 10,
  				'null' => TRUE,

  			),
  			'expired' => array(
  				'type' => 'TIMESTAMP',
  				'null' => FALSE,

  			),
  			'`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("auth_otp", TRUE);
  		$this->db->query('ALTER TABLE  `auth_otp` ENGINE = InnoDB');

  		## Create Table auth_token
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'user' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'hash' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 150,
  				'null' => FALSE,

  			),
  			'`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'expired_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("auth_token", TRUE);
  		$this->db->query('ALTER TABLE  `auth_token` ENGINE = InnoDB');

  		## Create Table banner
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'banner_type' => array(
  				'type' => 'TINYINT',
  				'constraint' => 1,
  				'null' => TRUE,
  				'default' => '1',

  			),
  			'banner_image' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'banner_subject' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'banner_content' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'activity' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 50,
  				'null' => FALSE,

  			),
  			'banner_order' => array(
  				'type' => 'INT UNSIGNED',
  				'null' => FALSE,

  			),
  			'destination' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'created_by' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("banner", TRUE);
  		$this->db->query('ALTER TABLE  `banner` ENGINE = InnoDB');

  		## Create Table cart_items
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'cart_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'id_promo_product' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'grosir_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'quantity' => array(
  				'type' => 'INT UNSIGNED',
  				'null' => FALSE,
  				'default' => '1',

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("cart_items", TRUE);
  		$this->db->query('ALTER TABLE  `cart_items` ENGINE = InnoDB');

  		## Create Table carts
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'motorist_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("carts", TRUE);
  		$this->db->query('ALTER TABLE  `carts` ENGINE = InnoDB');

  		## Create Table challenge
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'subject' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'activity' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'activity_target' => array(
  				'type' => 'INT UNSIGNED',
  				'null' => FALSE,
  				'default' => '1',

  			),
  			'activity_point' => array(
  				'type' => 'DOUBLE UNSIGNED',
  				'null' => FALSE,
  				'default' => '1',

  			),
  			'data' => array(
  				'type' => 'JSON',
  				'null' => TRUE,

  			),
  			'images' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'content' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'link' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'created_by' => array(
  				'type' => 'BIGINT UNSIGNED',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("challenge", TRUE);
  		$this->db->query('ALTER TABLE  `challenge` ENGINE = InnoDB');

  		## Create Table challenger
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'challenge_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'motorist_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'target_achieved' => array(
  				'type' => 'DOUBLE UNSIGNED',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'challenge_log' => array(
  				'type' => 'JSON',
  				'null' => TRUE,

  			),
  			'status' => array(
  				'type' => 'TINYINT UNSIGNED',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("challenger", TRUE);
  		$this->db->query('ALTER TABLE  `challenger` ENGINE = InnoDB');

  		## Create Table cities
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'province_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'city_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'city_name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("cities", TRUE);
  		$this->db->query('ALTER TABLE  `cities` ENGINE = InnoDB');

  		## Create Table districts
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'city_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'county_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'county_name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("districts", TRUE);
  		$this->db->query('ALTER TABLE  `districts` ENGINE = InnoDB');

  		## Create Table grosirs
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'area' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'area_service' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'credit_limit' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'available_budget' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'latitude' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'longitude' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'subdistrict_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'district_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'city_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'province_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("grosirs", TRUE);
  		$this->db->query('ALTER TABLE  `grosirs` ENGINE = InnoDB');

  		## Create Table inventories
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'grosir_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("inventories", TRUE);
  		$this->db->query('ALTER TABLE  `inventories` ENGINE = InnoDB');

  		## Create Table inventory_checkings
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'area' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'grosir' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'checker' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'carton_barcode' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'carton_image' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'pcs_barcode' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'pcs_image' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'sku_name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'qty' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'buy_price' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'sell_price' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'supplier' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'goods_condition' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'near_expire' => array(
  				'type' => 'DATE',
  				'null' => FALSE,

  			),
  			'near_expire_image' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("inventory_checkings", TRUE);
  		$this->db->query('ALTER TABLE  `inventory_checkings` ENGINE = InnoDB');

  		## Create Table inventory_products
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'inventory_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'product_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("inventory_products", TRUE);
  		$this->db->query('ALTER TABLE  `inventory_products` ENGINE = InnoDB');

  		## Create Table knex_migrations
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'INT UNSIGNED',
  				'null' => FALSE,
  				'auto_increment' => TRUE
  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'batch' => array(
  				'type' => 'INT',
  				'null' => TRUE,

  			),
  			'migration_time' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("knex_migrations", TRUE);
  		$this->db->query('ALTER TABLE  `knex_migrations` ENGINE = InnoDB');

  		## Create Table knex_migrations_lock
  		$this->dbforge->add_field(array(
  			'index' => array(
  				'type' => 'INT UNSIGNED',
  				'null' => FALSE,
  				'auto_increment' => TRUE
  			),
  			'is_locked' => array(
  				'type' => 'INT',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("index",true);
  		$this->dbforge->create_table("knex_migrations_lock", TRUE);
  		$this->db->query('ALTER TABLE  `knex_migrations_lock` ENGINE = InnoDB');

  		## Create Table logs
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'INT',
  				'null' => FALSE,
  				'auto_increment' => TRUE
  			),
  			'user_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'activities' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'activities_url' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'requests' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'responses' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'`action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("logs", TRUE);
  		$this->db->query('ALTER TABLE  `logs` ENGINE = InnoDB');

  		## Create Table motorist_devices
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'motorist_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'device_id' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'fcm_id' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'device_token' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'login_count' => array(
  				'type' => 'INT UNSIGNED',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'longitude' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 20,
  				'null' => TRUE,

  			),
  			'latitude' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 20,
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("motorist_devices", TRUE);
  		$this->db->query('ALTER TABLE  `motorist_devices` ENGINE = InnoDB');

  		## Create Table motorist_points
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'motorist_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'amount' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'expired_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("motorist_points", TRUE);
  		$this->db->query('ALTER TABLE  `motorist_points` ENGINE = InnoDB');

  		## Create Table motorist_target
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'motorist_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'target_month' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 20,
  				'null' => FALSE,

  			),
  			'target_amount' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'due_date' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("motorist_target", TRUE);
  		$this->db->query('ALTER TABLE  `motorist_target` ENGINE = InnoDB');

  		## Create Table user_motorist
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'email' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'phone' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'password' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'last_name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'avatar' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'url' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'birthdate' => array(
  				'type' => 'DATE',
  				'null' => TRUE,

  			),
  			'identity' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'identity_image' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'gender' => array(
  				'type' => 'ENUM("pria","wanita")',
  				'null' => TRUE,

  			),
  			'activated_at' => array(
  				'type' => 'DATETIME',
  				'null' => TRUE,

  			),
  			'activation_key' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'status' => array(
  				'type' => 'INT',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'address' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'province_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'city_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'district_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'subdistrict_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'zip_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 10,
  				'null' => TRUE,

  			),
  			'area_operational' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'sales_freq' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'notes' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'remember_token' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'last_location' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'latest_password' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'latest_channel' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 5,
  				'null' => FALSE,
  				'default' => 'sms',

  			),
  			'`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("user_motorist", TRUE);
  		$this->db->query('ALTER TABLE  `user_motorist` ENGINE = InnoDB');

  		## Create Table notifications
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'motorist_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'owner' => array(
  				'type' => 'JSON',
  				'null' => TRUE,

  			),
  			'activity' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'data' => array(
  				'type' => 'JSON',
  				'null' => TRUE,

  			),
  			'link' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'read_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'created_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("notifications", TRUE);
  		$this->db->query('ALTER TABLE  `notifications` ENGINE = InnoDB');

  		## Create Table pages
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'title' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'slug' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'content' => array(
  				'type' => 'TEXT',
  				'null' => FALSE,

  			),
  			'status' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'created_by' => array(
  				'type' => 'BIGINT',
  				'null' => FALSE,

  			),
  			'`created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_by' => array(
  				'type' => 'BIGINT',
  				'null' => TRUE,

  			),
  			'updated_at' => array(
  				'type' => 'DATETIME',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("pages", TRUE);
  		$this->db->query('ALTER TABLE  `pages` ENGINE = InnoDB');

  		## Create Table products
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'image' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("products", TRUE);
  		$this->db->query('ALTER TABLE  `products` ENGINE = InnoDB');

  		## Create Table promo_product
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'promo_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'product_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'price_before' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'comp_grosir' => array(
  				'type' => 'FLOAT',
  				'constraint' => 8,2,
  				'null' => FALSE,

  			),
  			'price_grosir' => array(
  				'type' => 'FLOAT',
  				'constraint' => 8,2,
  				'null' => FALSE,

  			),
  			'comp_motorist' => array(
  				'type' => 'FLOAT',
  				'constraint' => 8,2,
  				'null' => FALSE,

  			),
  			'price_motorist' => array(
  				'type' => 'FLOAT',
  				'constraint' => 8,2,
  				'null' => FALSE,

  			),
  			'comp_percent' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'price_comp' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'quantity' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'quantity_left' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'price_buy' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'subtotal' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'subtotal_before' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'subtotal_grosir' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'subtotal_motorist' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'subtotal_save' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("promo_product", TRUE);
  		$this->db->query('ALTER TABLE  `promo_product` ENGINE = InnoDB');

  		## Create Table promos
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'grosir_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'budget' => array(
  				'type' => 'INT',
  				'null' => FALSE,

  			),
  			'start_date' => array(
  				'type' => 'TIMESTAMP',
  				'null' => FALSE,

  			),
  			'end_date' => array(
  				'type' => 'TIMESTAMP',
  				'null' => FALSE,

  			),
  			'status' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'created_by' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'note' => array(
  				'type' => 'JSON',
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'note_manager' => array(
  				'type' => 'JSON',
  				'null' => TRUE,

  			),
  			'note_grosir' => array(
  				'type' => 'JSON',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("promos", TRUE);
  		$this->db->query('ALTER TABLE  `promos` ENGINE = InnoDB');

  		## Create Table provinces
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'province_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'country_id' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("provinces", TRUE);
  		$this->db->query('ALTER TABLE  `provinces` ENGINE = InnoDB');

  		## Create Table quotes
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'quotes' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'created_by' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("quotes", TRUE);
  		$this->db->query('ALTER TABLE  `quotes` ENGINE = InnoDB');

  		## Create Table site_options
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'option_name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 191,
  				'null' => FALSE,

  			),
  			'option_value' => array(
  				'type' => 'TEXT',
  				'null' => FALSE,

  			),
  			'option_type' => array(
  				'type' => 'ENUM("text","date","bool","file")',
  				'null' => FALSE,
  				'default' => 'text',

  			),
  			'autoload' => array(
  				'type' => 'TINYINT',
  				'null' => FALSE,
  				'default' => '1',

  			),
  			'created_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("site_options", TRUE);
  		$this->db->query('ALTER TABLE  `site_options` ENGINE = InnoDB');

  		## Create Table subdistricts
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'county_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'subcounty_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'subcounty_name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'zip_code' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => FALSE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("subdistricts", TRUE);
  		$this->db->query('ALTER TABLE  `subdistricts` ENGINE = InnoDB');

  		## Create Table users
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'area' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'deleted_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("users", TRUE);
  		$this->db->query('ALTER TABLE  `users` ENGINE = InnoDB');

  		## Create Table voucher_products
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'voucher_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'id_promo_product' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'product_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'before_price' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'price' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'quantity' => array(
  				'type' => 'INT',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'subtotal' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'comp_percent' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'total_save' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'status' => array(
  				'type' => 'TINYINT',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("voucher_products", TRUE);
  		$this->db->query('ALTER TABLE  `voucher_products` ENGINE = InnoDB');

  		## Create Table vouchers
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'motorist_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'id_promo' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'id_grosir' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'voucher_no' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'total_product' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'total_price' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'total_save' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,

  			),
  			'status' => array(
  				'type' => 'TINYINT',
  				'null' => FALSE,
  				'default' => '1',

  			),
  			'reasons' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'promo_type' => array(
  				'type' => 'TINYINT',
  				'null' => FALSE,
  				'default' => '1',

  			),
  			'expired_at' => array(
  				'type' => 'DATETIME',
  				'null' => TRUE,

  			),
  			'checkout_time' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,

  			),
  			'publish_at' => array(
  				'type' => 'DATETIME',
  				'null' => TRUE,

  			),
  			'publish_by' => array(
  				'type' => 'INT',
  				'null' => TRUE,

  			),
  			'sent_mail' => array(
  				'type' => 'TINYINT UNSIGNED',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  			'updated_by' => array(
  				'type' => 'INT',
  				'null' => TRUE,

  			),
  			'updated_type' => array(
  				'type' => 'ENUM("staff","superadmin","system")',
  				'null' => FALSE,
  				'default' => 'staff',

  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("vouchers", TRUE);
  		$this->db->query('ALTER TABLE  `vouchers` ENGINE = InnoDB');

  		## Create Table warung_sales
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'sales_order' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'warung_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'product_id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'date' => array(
  				'type' => 'DATE',
  				'null' => TRUE,

  			),
  			'qty' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'price' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'subtotal' => array(
  				'type' => 'DOUBLE',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'status' => array(
  				'type' => 'TINYINT',
  				'null' => FALSE,
  				'default' => '1',

  			),
  			'notes' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'expired_at' => array(
  				'type' => 'DATETIME',
  				'null' => TRUE,

  			),
  			'publish_at' => array(
  				'type' => 'DATETIME',
  				'null' => TRUE,

  			),
  			'publish_by' => array(
  				'type' => 'INT',
  				'null' => TRUE,

  			),
  			'sent_mail' => array(
  				'type' => 'TINYINT UNSIGNED',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'input_by' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_by' => array(
  				'type' => 'INT',
  				'null' => TRUE,

  			),
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("warung_sales", TRUE);
  		$this->db->query('ALTER TABLE  `warung_sales` ENGINE = InnoDB');

  		## Create Table warungs
  		$this->dbforge->add_field(array(
  			'id' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'email' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'phone' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'password' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'name' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'owner' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'avatar' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'slug' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'status' => array(
  				'type' => 'INT',
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'address' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'province' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'city' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'district' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'subdistrict' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => TRUE,

  			),
  			'latitude' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'longitude' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 255,
  				'null' => TRUE,

  			),
  			'areas' => array(
  				'type' => 'VARCHAR',
  				'constraint' => 100,
  				'null' => TRUE,

  			),
  			'notes' => array(
  				'type' => 'TEXT',
  				'null' => TRUE,

  			),
  			'is_retail' => array(
  				'type' => 'TINYINT',
  				'constraint' => 1,
  				'null' => FALSE,
  				'default' => '0',

  			),
  			'input_by' => array(
  				'type' => 'CHAR',
  				'constraint' => 36,
  				'null' => FALSE,

  			),
  			'`created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP DEFAULT_GENERATED',
  			'updated_at' => array(
  				'type' => 'TIMESTAMP',
  				'null' => TRUE,
  				'on update CURRENT_TIMESTAMP' => TRUE
  			),
  		));
  		$this->dbforge->add_key("id",true);
  		$this->dbforge->create_table("warungs", TRUE);
  		$this->db->query('ALTER TABLE  `warungs` ENGINE = InnoDB');
    }

    function make_base(){

          $this->load->library('Sqltoci');

          // All Tables:

          $this->sqltoci->generate();

          //Single Table:

          // $this->sqltoci->generate('table_name');

    }

}
