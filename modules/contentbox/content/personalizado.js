
/* Personalización para escribir los colores */
// Escribir el color seleccionado por defecto al cargar la página, si es que está activo el tema del color.
$(document).on( "ready", tituleador );
function tituleador()
{
	var Titulo_inicial = $( "#color_to_pick_list .selected a" ).attr( 'title' );
	if ( Titulo_inicial != undefined )
	{
		// $( "#attributes .attribute_fieldset:last-child .attribute_label").html("Color: " + Titulo_inicial);
		$( "#color_to_pick_list" ).parent().siblings(".attribute_label").html("Color: " + Titulo_inicial);
	}

	// Se cambia el nombre del color cuando se le hace click al color picker.
	$( "#color_to_pick_list li a" ).on( "click", Cambiador );
	function Cambiador()
	{
		var Titulo = $( this ).attr( "title" );
		// $("#attributes .attribute_fieldset:last-child .attribute_label").html("Color: " + Titulo);
		$( "#color_to_pick_list" ).parent().siblings( ".attribute_label" ).html( "Color: " + Titulo );
		console.log( "Color: " + Titulo );
	}
}

// Eliminar atributos de ancho y alto de las imágenes para muestra
$(document).on("ready", eliminador);
function eliminador()
{
	$("#attributes .attribute_list #color_to_pick_list li a.color_pick img").each(function()
	{ 
		$(this).removeAttr("width")
		$(this).removeAttr("height");
	});
}



// Para crear el famoso "ir hacia arriba"
$(document).on("ready", ir_arriba);
function ir_arriba()
{
	$("body").append('<div class="gotop"><a href="#" class="icon-arrow-up" title="Ir Arriba"></a></div>');
	$(window).scroll(function()
	{
		if ($(this).scrollTop() > 120) $('.gotop').fadeIn();
		else $('.gotop').fadeOut();
	});
	$(document).on("click",".gotop",function(e)
	{
		e.preventDefault();
		$("html, body").stop().animate({ scrollTop: 0 }, "slow");
	});
};


$(document).on( "ready", mensajeador );
function mensajeador()
{
	// Un mensaje personalizado en la barra de direcciónes.
	// $("#header nav").append('<span class="page-heading bottom-indent">Bienvenido a nuestro sitio de pruebas y desarrollo.</span>');

	// Para corregir el término "referencia" por "Código de Producto" 
	$( "#product_reference label").html("Código de Producto: ");
}


// Corregir el mensaje del módulo Mercado Pago
$(document).on( "ready", mercado_pago );
function mercado_pago()
{
	$("#is_mercadopago .payment_module div:nth-child(2) strong").html('Tarjeta de débito, crédito, Rapipago, Pago Fácil, Homebanking, Mercado Pago, etc...');
}

// Corregir el mensaje del módulo de transferencia bancaria
$(document).on( "ready", transferencia_bancaria );
function transferencia_bancaria()
{
	$(".bankwire span").html("Por CBU o por Número de Cuenta bancaria.");
}
