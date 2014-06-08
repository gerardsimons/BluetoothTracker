(function ()
{


    var mceUrl = '';
	// create pxShortcodes plugin
	tinymce.create("tinymce.plugins.pxShortcodes",
	{
		init: function ( ed, url )
		{
		    mceUrl = url;

			ed.addCommand("pxPopup", function ( a, params ){
                var title = 'Shortcode';

                if(typeof params.title != 'undefined')
                    title =  params.title;

                jQuery.pxmodal({
                    title: title,
                    url:   ajaxurl + "?action=px_sc_popup&type=" + params.type,
                    load:  jQuery.px.scpopup.load
                });
			});
		},
		createControl: function ( button, e )
		{
			if ( button != "px_button" )
                return null;
					
			// adds the tinymce button
			button = e.createMenuButton("px_button",
			{
				title: "Insert Shortcode",
				image: mceUrl + "/images/icon.png",
				icons: false
			});
			
            var plugin = this;	

			// adds the dropdown to the button
			button.onRenderMenu.add(function (c, b)
			{
                c = b.addMenu({ title: "Page Elements" });

                plugin.addPopupMenuButton(c, "Button", "button");
                plugin.addPopupMenuButton(c, "Parallax", "parallax");
                plugin.addPopupMenuButton(c, "Progressbar", "progressbar");
                plugin.addPopupMenuButton(c, "LayerSlider", "layerslider");
                plugin.addPopupMenuButton(c, "Sidebar", "sidebar");
                plugin.addPopupMenuButton(c, "Contact Form 7", "cf7");

                c.addSeparator();

                plugin.addPopupMenuButton(c, "Google Map", "gmap");
                plugin.addPopupMenuButton(c, "Google Map Marker", "gmap_marker");

                c.addSeparator();

                plugin.addPopupMenuButton(c, "IconBox", "iconbox");
                plugin.addPopupMenuButton(c, "IconBox Shape", "iconbox_shape");

                c.addSeparator();

                plugin.addPopupMenuButton(c, "Separator", "separator");
                plugin.addPopupMenuButton(c, "Title Separator", "title_separator");

                c.addSeparator();

                plugin.addPopupMenuButton(c, "Post Slider", "post_slider");

                c.addSeparator();

                plugin.addPopupMenuButton(c, "Portfolio Grid", "portfolio");
                plugin.addPopupMenuButton(c, "Portfolio Slider", "portfolio_slider");

                c.addSeparator();

                plugin.addPopupMenuButton(c, "Team Member", "team_member");
                plugin.addPopupMenuButton(c, "Team Member Icon", "team_icon");

                c.addSeparator();

                plugin.addImmediateMenuButton(c, "Testimonial Group", "[testimonial_group]<br />[/testimonial_group]");
                plugin.addPopupMenuButton(c, "Testimonial", "testimonial");

                c.addSeparator();

                plugin.addImmediateMenuButton(c, "Carousel", "[carousel items_visible='4']<br />[/carousel]");
                plugin.addPopupMenuButton(c, "Carousel Item", "carousel_item");

                c = b.addMenu({ title: "Tabs and Toggles" });

                plugin.addImmediateMenuButton(c, "Tab Group", "[tab_group]<br />[/tab_group]");
                plugin.addImmediateMenuButton(c, "Tab", "[tab title='Tab'][/tab]");

                c.addSeparator();

                plugin.addImmediateMenuButton(c, "Horizontal Tab Group", "[horizontal_tab_group title_color='']<br />[/horizontal_tab_group]");
                plugin.addImmediateMenuButton(c, "Horizontal Tab", "[horizontal_tab title='Tab'][/horizontal_tab]");

                c.addSeparator();

                plugin.addImmediateMenuButton(c, "Accordion", "[accordion]<br />[/accordion]");
                plugin.addPopupMenuButton(c, "Accordion Tab", "accordion_tab");

                c.addSeparator();

                plugin.addImmediateMenuButton(c, "Toggle", "[toggle]<br />[/toggle]");
                plugin.addPopupMenuButton(c, "Toggle Tab", "toggle_tab");


                c = b.addMenu({ title: "Layout Elements" });

                plugin.addImmediateMenuButton(c, "1/2 + 1/2", "[row]<br />[span6][/span6]<br />[span6][/span6]<br />[/row]");
                plugin.addImmediateMenuButton(c, "1/3 + 1/3 + 1/3", "[row]<br />[span4][/span4]<br />[span4][/span4]<br />[span4][/span4]<br />[/row]");
                plugin.addImmediateMenuButton(c, "2/3 + 1/3", "[row]<br />[span8][/span8]<br />[span4][/span4]<br />[/row]");
                plugin.addImmediateMenuButton(c, "1/4 + 1/4 + 1/4 + 1/4", "[row]<br />[span3][/span3]<br />[span3][/span3]<br />[span3][/span3]<br />[span3][/span3]<br />[/row]");
                plugin.addImmediateMenuButton(c, "3/4 + 1/4", "[row]<br />[span9][/span9]<br />[span3][/span3]<br />[/row]");

                c.addSeparator();

                plugin.addImmediateMenuButton(c, "Container", "[container]<br />[/container]");
                plugin.addImmediateMenuButton(c, "Alternate Section", "[section_alt background_color='']<br />[/section_alt]");

                c.addSeparator();

                plugin.addImmediateMenuButton(c, "Row", "[row]<br />[/row]");

                for(var i=1; i<=12;i++)
                    plugin.addImmediateMenuButton(c, "Span " + i, "[span"+i+" offset=''][/span"+i+"]");
			});
				
			return button;
		},
		addPopupMenuButton: function ( ed, title, id ) {
			ed.add({
				title: title,
				onclick: function () {
					tinyMCE.activeEditor.execCommand("pxPopup", false, { title: title, type: id } )
				}
			})
		},
		addImmediateMenuButton: function ( ed, title, sc) {
			ed.add({
				title: title,
				onclick: function () {
					tinyMCE.activeEditor.execCommand( "mceInsertContent", false, sc )
				}
			})
		},
		getInfo: function () {
			return {
				longname: 'PixFlow Shortcodes',
				author: 'Mohsen Heydari',
				authorurl: 'http://themeforest.net/user/pixflow/',
				infourl: 'http://wiki.moxiecode.com/',
				version: "1.0"
			}
		}
	});
	
	// add pxShortcodes plugin
	tinymce.PluginManager.add("pxShortcodes", tinymce.plugins.pxShortcodes);
})();