$( document ).ready(function() {

	$('#find-date').datepicker();
	
	$('#refresh-warehouse-list').click(function(){
		///TODO: mockot kicserélni
		var warehouseTableTemplate = _.template($('#template_warehouse_table').html());
		
		var rows = [];
		rows.push ({name: 'Szekrény',
					goodsType: 'Butor',
					putInDate: '2016.05.05', 
					putInOperationId: '632',
					putOutDate: '2016.06.01',
					putOutOperationId: '783'
				});
		rows.push({
					name: 'Hűtőszekrény',
					goodsType: 'Konyha',
					putInDate: '2016.06.05', 
					putInOperationId: '712',
					putOutDate: null,
					putOutOperationId: null
				});
				
		$('#warehouse-table > tbody').html(
				warehouseTableTemplate({rows: rows})
		);
		
		setWarehouseTableFormat();
	});
	
	$('#refresh-warehouse-list').trigger('click');

});

function setWarehouseTableFormat(){
	var defaultLength = 10;
	if(typeof(Storage) !== "undefined") {
		if (!isNaN(localStorage.warehouse_list_default_page_length) && (localStorage.warehouse_list_default_page_length != "")){
			defaultLength = localStorage.warehouse_list_default_page_length;
		}
	}
	
	$('#warehouse-table').DataTable({
		"retrieve": true,
        "searching":   false,
        "iDisplayLength" : defaultLength,
        "language": {
	        "lengthMenu": "Találatok _MENU_ db/oldal <br>",
            "zeroRecords": "Nincs találat",
            "info": " _PAGE_ / _PAGES_",
            "infoEmpty": "Nincs találat",
            "infoFiltered": "(filtered from _MAX_ total records)",
            "paginate": 
            {
				"next": "Következő",
				"previous": "Előző"
             }
        }
    });
	
	$('#warehouse-table_length').css('margin-bottom',  '10px');	
	$('select[name="warehouse-table_length"]').change(function(){
		if(typeof(Storage) !== "undefined") {
			localStorage.setItem("warehouse_list_default_page_length", $(this).val());
		}
	});
	
	$('#warehouse-table').show();
}

function openOperation(id){
	alert('Itt majd megmutatjuk felugró ablakban, a kérvény vagy felajánlás részleteit, szerkesztési lehetőség nélkül');
}

	



