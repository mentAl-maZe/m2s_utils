<?php
/**
 * M2S Utils MetaBox
 *
 * Add theme specific configuration options to the backend
 *
 * Based on Twenty seventeen by WordPress team
 *
 * @package    Muwit\MediaSlider
 * @subpackage Classes
 * @since      1.0
 */

namespace M2S\Utils\Backend;


class MetaBox {
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var callable
	 */
	private $fields;

	/**
	 * @var bool
	 */
	private $single;

	/**
	 * @var string
	 */
	private $capabilityType;

	/**
	 * @var string
	 */
	private $nonceId;

	/**
	 * @var string
	 */
	private $saveId;

	/**
	 * MetaBoxUtils constructor.
	 *
	 * @param string        $id
	 * @param string        $title
	 * @param callable|null $fields
	 * @param bool          $single
	 * @param string        $capabilityType
	 */
	function __construct( string $id, string $title = '', $fields = null, bool $single = false, string $capabilityType = 'post') {
		$this->id = $id;

		$this->setTitle( $title );
		$this->setFields( $fields );
		$this->setSingle($single);
		$this->setCapabilityType( $capabilityType );
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 *
	 * @return MetaBox
	 */
	public function setId( string $id ): MetaBox {
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		if (!$this->title) {
			$this->title = implode(
				' ',
				array_map(
					function($el) {
						return ucfirst($el);
					},
					explode(
						'_',
						$this->getId()
					)
				)
			);
		}

		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return MetaBox
	 */
	public function setTitle( string $title ): MetaBox {
		$this->title = $title;

		return $this;
	}

	/**
	 * @return callable|null
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @param callable|array $fields
	 *
	 * @return MetaBox
	 */
	public function setFields( $fields ): MetaBox {
		/** @TODO: Add sanitation */
		$this->fields = $fields;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSingle(): bool {
		return $this->single;
	}

	/**
	 * @param bool $single
	 *
	 * @return MetaBox
	 */
	public function setSingle( bool $single ): MetaBox {
		$this->single = $single;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCapabilityType(): string {
		return $this->capabilityType ?: 'post';
	}

	/**
	 * @param string $capabilityType
	 *
	 * @return MetaBox
	 */
	public function setCapabilityType( string $capabilityType ): MetaBox {
		$this->capabilityType = $capabilityType;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNonceId(): string {
		return $this->nonceId ?: $this->getId() . '_nonce';
	}

	/**
	 * @param string $nonceId
	 *
	 * @return MetaBox
	 */
	public function setNonceId( string $nonceId ): MetaBox {
		$this->nonceId = $nonceId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSaveId(): string {
		return $this->saveId ?: $this->getId() . '_save';
	}

	/**
	 * @param string $saveId
	 *
	 * @return MetaBox
	 */
	public function setSaveId( string $saveId ): MetaBox {
		$this->saveId = $saveId;

		return $this;
	}

	/**
	 * @param \WP_Screen|array|string|null  $screen
	 * @param string                        $context
	 * @param string                        $priority
	 */
	public function add( $screen = null, string $context = 'advanced', string $priority = 'default' ) {
		add_meta_box(
			$this->getId(),
			$this->getTitle(),
			function ( \WP_Post $post, array $metaBox = array() ) {
				$value = get_post_meta( $post->ID, '_' . $metaBox['args']['id'], $metaBox['args']['single'] );

				wp_nonce_field(
					$metaBox['args']['saveId'],
					$metaBox['args']['nonceId']
				);

				if (is_callable($callback = $metaBox['args']['fields'])) {
					echo call_user_func( $callback, $post, $metaBox, $value );
				} elseif (is_array($callback)) {
					/** @TODO: Add preconfigured fields */
				} else {
					echo '<input type="text" id="' . $metaBox['args']['id'] . '-field" name="' . $metaBox['args']['id'] . '" value="' . $value . '" />';
				}
			},
			$screen,
			$context,
			$priority,
			array(
				'id' => $this->getId(),
				'title' => $this->getTitle(),
				'fields' => $this->getFields(),
				'single' => $this->isSingle(),
				'capabilityType' => $this->getCapabilityType(),
				'nonceId' => $this->getNonceId(),
				'saveId' => $this->getSaveId()
			)
		);
	}

	/**
	 * @param \WP_Screen|array|string|null  $screen
	 * @param string                        $context
	 */
	public function remove($screen = null, string $context = 'advanced') {
		remove_meta_box(
			$this->getId(),
			$screen,
			$context
		);
	}

	/**
	 * @param int    $postId
	 * @param string $nonce
	 */
	public function save( int $postId, string $nonce = '' ) {
		$id = $this->getId();
		$nonceId = $this->getNonceId();
		if (!$nonce) {
			if (!isset($_POST[$nonceId])) {
				return;
			}

			$nonce = $_POST[$nonceId];
		}

		if (!wp_verify_nonce( $nonce, $id . '_save' )) {
			return;
		}

		if (
			current_user_can( 'edit_' . $this->getCapabilityType(), $postId ) &&
			isset( $_POST[ $id ] )
		) {
			update_post_meta( $postId, '_' . $id, $_POST[ $id ] );
		}
	}

	/**
	 * @param string|array $postTypes
	 */
	public function registerSaveAction( $postTypes = '' ) {
		if (!is_array($postTypes)) {
			$postTypes = array($postTypes);
		}

		foreach (array_unique($postTypes) as $postType) {
			add_action( 'save_post' . ( $postType ? '_' . $postType : '' ), array(
				$this,
				'save'
			) );
		}
	}

	/**
	 * @param string $postType
	 */
	public function unregisterSaveAction( string $postType = '' ) {
		remove_action( 'save_post' . ( $postType ? '_' . $postType : '' ), array(
			$this,
			'save'
		) );
	}
}