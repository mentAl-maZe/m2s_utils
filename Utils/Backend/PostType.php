<?php
/**
 * Created by PhpStorm.
 * User: main
 * Date: 20/05/17
 * Time: 5.24
 */

namespace M2S\Utils\Backend;


class PostType {
	/**
	 * @var string
	 */
	private $typeId;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var array
	 */
	private $metaBoxes;

	/**
	 * @var array
	 */
	private $metaBoxPositions;

	/**
	 * PostType constructor.
	 *
	 * @param string $typeId
	 * @param array  $config
	 * @param array  $metaBoxes
	 */
	public function __construct( string $typeId, array $config = array(), array $metaBoxes = array() ) {
		$this->setTypeId($typeId);

		$this->setConfig($config, get_post_type_object($this->typeId));

		$this->metaBoxes = $metaBoxes;
	}

	/**
	 * @return string
	 */
	public function getTypeId(): string {
		return $this->typeId;
	}

	/**
	 * @param string $typeId
	 *
	 * @return PostType
	 */
	public function setTypeId( string $typeId ): PostType {
		$this->typeId = $typeId;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getConfig(): array {
		$config = $this->config;

		if (count($this->getMetaBoxes()) > 0) {
			$config['register_meta_box_cb'] = array($this, 'registerMetaBoxes');
		}

		return $config;
	}

	/**
	 * @param array              $config
	 * @param \WP_Post_Type|null $typeObject
	 *
	 * @return PostType
	 */
	public function setConfig(array $config, \WP_Post_Type $typeObject = null): PostType {
		$validConfigs = array(
			'description',
			'public',
			'hierarchical',
			'exclude_from_search',
			'publicly_queryable',
			'show_ui',
			'show_in_menu',
			'show_in_nav_menus',
			'show_in_admin_bar',
			'menu_position',
			'menu_icon',
			'capability_type',
			'capabilities',
			'map_meta_cap',
			'supports',
			'taxonomies',
			'has_archive',
			'rewrite',
			'query_var',
			'can_export',
			'delete_with_user',
			'show_in_rest',
			'rest_base',
			'rest_controller_class'
		);
		$validLabels = array(
			'name',
			'singular_name',
			'add_new',
			'add_new_item',
			'edit_item',
			'new_item',
			'view_item',
			'view_items',
			'search_items',
			'not_found',
			'not_found_in_trash',
			'parent_item_colon',
			'all_items',
			'archives',
			'attributes',
			'insert_into_item',
			'uploaded_to_this_item',
			'featured_image',
			'set_featured_image',
			'remove_featured_image',
			'use_featured_image',
			'menu_name',
			'filter_items_list',
			'items_list_navigation',
			'items_list',
			'name_admin_bar'
		);

		if (isset($config['labels'])) {
			$labels = $config['labels'];
			unset($config['labels']);
		} else {
			$labels = array();
		}

		foreach ($validConfigs as $property) {
			if (isset($config[$property])) {
				$this->config[$property] = $config[$property];
			} elseif ($typeObject && property_exists($typeObject, $property) && isset($typeObject->$property)) {
				$this->config[$property] = $typeObject->$property;
			}
		}

		if ($typeObject) {
			$this->config['labels'] = get_post_type_labels($typeObject);
		} else {
			$this->config['labels'] = array();
		}

		foreach ($validLabels as $label) {
			if (isset($labels[$label])) {
				$this->config['labels'][$label] = $labels[$label];
			}
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getMetaBoxes(): array {
		return $this->metaBoxes;
	}

	/**
	 * @param array $metaBoxes
	 *
	 * @return PostType
	 */
	public function setMetaBoxes( array $metaBoxes ): PostType {
		$this->metaBoxes = $metaBoxes;

		return $this;
	}

	/**
	 * @param MetaBox $metaBox
	 *
	 * @return PostType
	 */
	public function addMetaBox( MetaBox $metaBox ): PostType {
		if (!in_array($metaBox, $this->metaBoxes)) {
			$this->metaBoxes[] = $metaBox;
		}

		return $this;
	}

	/**
	 * @param MetaBox $metaBox
	 *
	 * @return PostType
	 */
	public function removeMetaBox( MetaBox $metaBox ): PostType {
		if ($index = array_search($metaBox, $this->metaBoxes)) {
			array_splice($this->metaBoxes, $index, 1);
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getMetaBoxPositions(): array {
		return $this->metaBoxPositions;
	}

	/**
	 * @param array $metaBoxPositions
	 *
	 * @return PostType
	 */
	public function setMetaBoxPositions( array $metaBoxPositions ): PostType {
		/** @TODO: add sanitation */
		$this->metaBoxPositions = $metaBoxPositions;

		return $this;
	}

	/**
	 * @param string $metBoxId
	 * @param array  $position
	 *
	 * @return PostType
	 */
	public function setMetaBoxPosition(string $metBoxId, array $position = array()): PostType {
		/** @TODO: add sanitation */
		if (!$position && isset($this->metaBoxPositions[$metBoxId])) {
			unset($this->metaBoxPositions[$metBoxId]);
		} else {
			$this->metaBoxPositions[$metBoxId] = $position;
		}

		return $this;
	}

	public function registerMetaBoxes() {
		/** @var MetaBox $metaBox */
		foreach ($this->metaBoxes as $metaBox) {
			if (isset($this->metaBoxPositions[$metaBox->getId()])) {
				$position = $this->metaBoxPositions[$metaBox->getId()];
			} else {
				$position = array('advanced', 'default');
			}

			$metaBox->add($this->typeId, $position[0], $position[1]);
		}
	}

	public function registerPostType() {
		register_post_type($this->getTypeId(), $this->getConfig());
	}

	public function update($registerSaves = true) {
		add_action( 'init', array($this, 'registerPostType') );

		if ($registerSaves && count($metaBoxes = $this->getMetaBoxes()) > 0) {
			$screen = $this->getTypeId();
			/** @var MetaBox $metaBox */
			foreach ($metaBoxes as $metaBox) {
				$metaBox->registerSaveAction($screen);
			}
		}
	}
}