(function ()
{
	function createStyleElement(text)
	{
		var style       = document.createElement('style');
		style.type      = 'text/css';
		style.innerHTML = text;
		document.getElementsByTagName('head')[0].appendChild(style);
	}
	
	function addPopupMenuButton ( title, id ) 
	{
		return {text: title, onclick: function(){
                jQuery.pxmodal({
                    title: title,
                    url:   ajaxurl + "?action=px_sc_popup&type=" + id,
                    load:  jQuery.px.scpopup.load
                });
		}};
	}
	
	function addImmediateMenuButton (ed, title, text ) 
	{
		return {text: title, onclick: function(){
            ed.insertContent(text);
		}};
	}

	tinymce.PluginManager.add( 'pxShortcodes', function( editor, url ) {
		//Create icon class
		createStyleElement('.mce-i-px-btn{ background-image: url('+url+'/images/icon.png) !important; }');
	
		var menus = [ 
		{text:"Page Elements", menu:[
			addPopupMenuButton('Button', 'button'),
			addPopupMenuButton("Parallax", "parallax"),
            addPopupMenuButton("Progressbar", "progressbar"),
            addPopupMenuButton("LayerSlider", "layerslider"),
            addPopupMenuButton("Sidebar", "sidebar"),
            addPopupMenuButton("Contact Form 7", "cf7"),
			
			//No separator??
			
			addPopupMenuButton("Google Map", "gmap"),
            addPopupMenuButton("Google Map Marker", "gmap_marker"),
			
			addPopupMenuButton("IconBox", "iconbox"),
            addPopupMenuButton("IconBox Shape", "iconbox_shape"),
			
			addPopupMenuButton("Separator", "separator"),
            addPopupMenuButton("Title Separator", "title_separator"),
			
			addPopupMenuButton("Post Slider", "post_slider"),
			
			addPopupMenuButton("Portfolio Grid", "portfolio"),
            addPopupMenuButton("Portfolio Slider", "portfolio_slider"),
			
			addPopupMenuButton("Team Member", "team_member"),
            addPopupMenuButton("Team Member Icon", "team_icon"),
			
			addImmediateMenuButton(editor, "Testimonial Group", "[testimonial_group]<br />[/testimonial_group]"),
            addPopupMenuButton("Testimonial", "testimonial"),
			
			addImmediateMenuButton(editor, "Carousel", "[carousel items_visible='4']<br />[/carousel]"),
            addPopupMenuButton("Carousel Item", "carousel_item")
		]},
		{text: "Tabs and Toggles", menu:[
			addImmediateMenuButton(editor, "Tab Group", "[tab_group]<br />[/tab_group]"),
            addImmediateMenuButton(editor, "Tab", "[tab title='Tab'][/tab]"),
			
			addImmediateMenuButton(editor, "Horizontal Tab Group", "[horizontal_tab_group title_color='']<br />[/horizontal_tab_group]"),
            addImmediateMenuButton(editor, "Horizontal Tab", "[horizontal_tab title='Tab'][/horizontal_tab]"),
			
			addImmediateMenuButton(editor, "Accordion", "[accordion]<br />[/accordion]"),
            addPopupMenuButton("Accordion Tab", "accordion_tab"),
			
			addImmediateMenuButton(editor, "Toggle", "[toggle]<br />[/toggle]"),
            addPopupMenuButton("Toggle Tab", "toggle_tab")
		]},
		{text: "Layout Elements", menu:[
			addImmediateMenuButton(editor, "1/2 + 1/2", "[row]<br />[span6][/span6]<br />[span6][/span6]<br />[/row]"),
            addImmediateMenuButton(editor, "1/3 + 1/3 + 1/3", "[row]<br />[span4][/span4]<br />[span4][/span4]<br />[span4][/span4]<br />[/row]"),
            addImmediateMenuButton(editor, "2/3 + 1/3", "[row]<br />[span8][/span8]<br />[span4][/span4]<br />[/row]"),
            addImmediateMenuButton(editor, "1/4 + 1/4 + 1/4 + 1/4", "[row]<br />[span3][/span3]<br />[span3][/span3]<br />[span3][/span3]<br />[span3][/span3]<br />[/row]"),
            addImmediateMenuButton(editor, "3/4 + 1/4", "[row]<br />[span9][/span9]<br />[span3][/span3]<br />[/row]"),
			
			addImmediateMenuButton(editor, "Container", "[container]<br />[/container]"),
            addImmediateMenuButton(editor, "Alternate Section", "[section_alt background_color='']<br />[/section_alt]"),
			
			addImmediateMenuButton(editor, "Row", "[row]<br />[/row]")
		]}
		];//menus
		
		//Add span shortcodes
		for(var i=1; i<=12;i++)      
			menus[2].menu.push(addImmediateMenuButton(editor, "Span " + i, "[span"+i+" offset=''][/span"+i+"]"));
	
		editor.addButton('px_button', {
			type: 'menubutton',
			icon: 'px-btn',
			menu: menus
		});//addButton
	});//plugin

})();