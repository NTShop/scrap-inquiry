/**
 * Admin scripts
 *
 * @package Scrap_Enquiry
 */
( function( $ ) {
	$( function() {

		// Add new selectable scrap item to the table. 
		$( '.add_selecteable_scrap_item').on( 'click', () => {
			let row = $( '.scrap_items_table tbody tr:first').clone( true );
			row.find( 'input' ).each( function() {
				$( this ).val( '' );
			});
			row.find( 'select option' ).removeAttr( 'selected' );

			$( '.scrap_items_table tbody' ).append( row );
		});

		// Add new selectable scrap item to the table. 
		$( '.add_arbitrary_scrap_item').on( 'click', () => {
			let row = $( '.scrap_items_table tbody tr:first').clone( true );
			row.find( 'input' ).each( function() {
				$( this ).val( '' );
			});
			row.find( 'select' ).replaceWith( '<input type="text" name="items[name][]" value="">' );
			$( '.scrap_items_table tbody' ).append( row );
		});

		// Remove scrap item from table.
		$( '.remove_scrap_item').on( 'click', function() {
			$( this ).closest( 'tr' ).remove();
			$( '.scrap_item_value' ).each( function() { 
				$( this ).trigger( 'change' ); 
			});
		});
		
		// Calculate subtotal when item value changes.
		$( '.scrap_item_value' ).on( 'change', () => {
			let subtotal = 0;
			$( '.scrap_item_value' ).each( function() {
				let item_value = parseFloat( $( this ).val() );
				if ( item_value <= 0 || isNaN( item_value ) ) {
					return;
				}
				subtotal += ( item_value );
			});
			$( '.scrap_value_total') .val( subtotal.toFixed(2) );
		});

		// Calculate total when item weight changes.
		$( '.scrap_item_weight' ).on( 'change', () => {
			calculate_scrap_value();
			$( '.scrap_item_value' ).trigger( 'change' );
		});

		// Calculate total when item name changes.
		$( '.scrap_item_name' ).on( 'change', () => {
			calculate_scrap_value();
			$( '.scrap_item_value' ).trigger( 'change' );
		});

		// Calculate item values into a total.
		let calculate_scrap_value = () => {
			$( 'table.scrap_items_table tbody tr' ).each( function() {
				let weight = $( this ).find( '.scrap_item_weight' ).val();
				if ( parseFloat( weight ) <= 0 || isNaN( parseFloat( weight) ) ) {
					$( this ).find( '.scrap_item_value' ).val( '' );
					return;
				}
				let base_value = $( this ).find( '.scrap_item_name option:selected' ).data( 'value' );
				// Calculate item's total value of the item from the value in the selected name element multipled by the weight/quantity.
				let value = parseFloat( weight ) * parseFloat( base_value );
				if ( parseFloat( value ) >=  0 && !isNaN( value ) ) {
					$( this ).find( '.scrap_item_value' ).val( value.toFixed(2) );
				}
			});
		}
	});
})( jQuery );
