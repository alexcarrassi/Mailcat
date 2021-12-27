/** Javascript for the Ark_Mail CPT page **/

console.log(ark_mail_cpt_config);
var Ark_Mail_CPT_JS = function() {

    var self = this;

    self.create = function() {

        self.datalinks = ark_mail_cpt_config.datalinks;
        self.format_functions = ark_mail_cpt_config.format_functions;
        self.msg = ark_mail_cpt_config.msg;
        self.grapes_blocks = ark_mail_cpt_config.editor.blocks;
        self.grapes_icons = ark_mail_cpt_config.editor.icons;
        self.var_source = ark_mail_cpt_config.variables;

        /**
         * Prevent the Post form from submitting when press the Enter key
         */
        document.querySelector("form#post").addEventListener("keydown", function(e) {
            if(e.keyCode === 13) {
                e.preventDefault();
            }
        })

        /** Navbar **/
        document.querySelector(".mail_composer_navbar ").addEventListener('click', self.navigation);
        window.addEventListener("click", function(e){
            //Close the dropdown
            self.hide_dropdown_elements();
        });

        /** Datalinks tab **/

            //Add the 'active' class to the first datalink
        var first_row = document.querySelector(".datalink_row");
        if(first_row !== null) {
            first_row.classList.add("active");
        }

        document.querySelector("#tab_datalinks").addEventListener('click', self.click_delegation, false)
        document.querySelector("#dialog_add_datalink").addEventListener('click', self.click_delegation, false)
        document.querySelector("#dialog_add_datalink").addEventListener('change', self.change_delegation, false );
        document.querySelector(".variable_set_display").addEventListener('input', self.input_delegation, false);
        document.querySelector(".variable_set_display").addEventListener('drag', self.drag_delegation, true );
        document.querySelector(".variable_set_display").addEventListener('drop', self.drop_delegation, true );
        document.querySelector(".variable_set_display").addEventListener('dragover', self.dragover_delegation, true );

        var datalink_variables = document.querySelectorAll(".datalink_variable:not(.variables_container_header)");
        datalink_variables.forEach(function(datalink_variable) {
            self.format_variable(datalink_variable);
        });


        document.querySelector(".variable_set_display").addEventListener('input', self.input_delegation, false);
        document.querySelector(".variable_set_display").addEventListener('keydown', self.keyup_delegation, false);


        /** Content Creation tab  **/
        self.initialize_editor();

        document.querySelector("#btn_direct_mail").addEventListener("click", self.direct_mail);

        self.initialize_errorlog_tab();
    }

    self.direct_mail = function() {
        var data = {};
        data['mail_id'] = ark_mail_cpt_config.mail_id;
        data['recipient'] = document.querySelector("#input_direct_mail").value;
        data['root_ids'] = {};
        document.querySelectorAll("#direct_mail_id_inputs input").forEach(function(input) {
            data['root_ids'][input.name] = input.value;
        });

        data['action'] = 'direct_mail';

        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: data,
            success : function(response) {
                if(response['success']) {
                    alert("done")
                }
                else {
                    console.log(response);
                }
            }
        });
    }


    self.component_style_change = function(property) {
        // var selected = self.editor.getSelected();
        //
        // if(selected.changed.style !== undefined && selected.changed.style[property] !== undefined) {
        //     var view = selected.getView();
        //
        //     var arr = view.attributes.style.split(";");
        //     var style_ob = {};
        //     for(var i  = 0; i < arr.length; i++) {
        //         let element = arr[i].trim();
        //         let split = arr[i].split(/:(.+)/);
        //
        //         if(split[0].trim() !== "" && split[1].trim() !== "") {
        //             style_ob[split[0]] = split[1].trim();
        //         }
        //     }
        //
        //     style_ob[property] = selected.changed.style[property];
        //
        //     var style_string = "";
        //     Object.keys(style_ob).forEach(function(key) {
        //         style_string += key + ":" + style_ob[key] + ";";
        //     });
        //
        //     view.attributes.style = style_string;
        // }
        //
        //
        // selected.getView().renderStyle();
    }
    /** Content Creation tab **/
    self.initialize_editor = function() {

        // let components = JSON.parse(ark_mail_cpt_config.editor.components);
        // let style = JSON.parse(ark_mail_cpt_config.editor.styles);
        self.editor = grapesjs.init({
            avoidDefaults : true,
            // Indicate where to init the editor. You can also pass an HTMLElement
            container: '#gjs',
            // Get the content for the canvas directly from the element
            // As an alternative we could use: `components: '<h1>Hello World Component!</h1>'`,
            fromElement: false,
            style:ark_mail_cpt_config.editor.style || ark_mail_cpt_config.editor.css,

            // Size of the editor
            width: 'auto',
            height: '1200px',
            storageManager: {
                type: 'remote',
                autoload: false,
                autosave: true,
                stepsBeforeSave: 1,
                urlStore: ark_mail_cpt_config.ajax_url,
                // For custom parameters/headers on requests
                // params: { action: 'save_mail_template' },
                // headers: { Authorization: 'Basic ...' },
            },
            avoidInlineStyle: false,
            plugins: ['grapesjs-mjml',
                'gjs-plugin-ckeditor'
            ],
            pluginsOpts: {
                'gjs-plugin-ckeditor': {
                    position: 'left',
                    options: {
                        enterMode: CKEDITOR.ENTER_BR,
                        startupFocus: true,
                        extraAllowedContent: '*(*);*{*}', // Allows any class and any inline style
                        allowedContent: true, // Disable auto-formatting, class removing, etc.
                        language: 'nl',
                        extraPlugins: 'sharedspace,font',
                        toolbar: [
                            {name: 'Styles', items: ['Font', 'FontSize', 'lineheight']},
                            {name: 'ClipBoard', items : ['Copy', 'Cut', 'Paste', '-', 'Undo', 'Redo']},
                            // {name: 'Insert', items: ['Image', 'Table']},
                            {name: 'Editing', items: ['Scayt']},
                            {name: 'Basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat']},
                            {name: 'Paragraph', items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'JustifyLeft', 'JustifyCenter', 'JustifyRight']},
                            {name: 'colors', items: [ 'TextColor', 'BGColor' ] }

                        ],
                    }
                },
                'grapesjs-mjml' : {
                    options: {
                        resetStyleManager : false
                    }
                }

            },
            // Avoid any default panel
            defaults: {
                toolbar: [{
                    attributes: {class: 'fa fa-th-large'},
                    command: 'testcommand',
                }]
            },
            panels : {
                defaults : [{

                    id: 'creation_tools_nav',
                    el: '.creation_tools_nav',
                    buttons: [
                        {
                            id: 'show_blocks',
                            active: true,
                            label: 'Blocks',
                            command: 'show_blocks',
                            togglable: false
                        },
                        {
                            id: 'show_variables',
                            active: true,
                            label: 'variables',
                            command: 'show_variables',
                            toggleable: false
                        },
                        {
                            id: 'show_styles',
                            active: true,
                            label: 'Styles',
                            command: 'show_styles',
                            toggleable: false
                        },
                        {
                            id: 'show-traits',
                            active: true,
                            label: 'Traits',
                            command: 'show-traits',
                            togglable: false,
                        }
                    ],
                }]
            },
            blockManager: {
                appendTo: '.blocks',
            },
            styleManager : {
                appendTo: '.styles',
                sectors: [{
                    name: 'Dimension',
                    buildProps: ['width', 'min-height']
                },{
                    name: 'Extra',
                    buildProps: ['background-color', 'box-shadow']
                }]
            },
            assetManager: {
                assets: ark_mail_cpt_config.media_assets,
                // Upload endpoint, set `false` to disable upload, default `false`
                upload: ark_mail_cpt_config.media_endpoint,
                // Custom headers to pass with the upload request
                headers: {},
                // Custom parameters to pass with the upload request, eg. csrf token
                params: {'test' : 1,
                    'post_id' : ark_mail_cpt_config.mail_id,
                    'action' : 'upload-attachment',
                    '_ajax_nonce' : ark_mail_cpt_config.nonce_media_form,
                },
                multiUpload: false,
                autoAdd: false, //We need custom handling of wordpress data
                // The credentials setting for the upload request, eg. 'include', 'omit'
                credentials: 'include',
                // The name used in POST to pass uploaded files, default: `'files'`
                uploadName: 'async-upload',
            },
            traitManager: {
                appendTo: '.traits',
            }
        });




        // Do something on response
        self.editor.on('asset:upload:response', (response) => {
            self.editor.AssetManager.add([
                {
                    src: response.data.url,
                    type: 'image',
                    height: response.data.height,
                    width: response.data.width,
                }
            ])
        });



        var timerId;

        /** Overriding (debouncing) the .store method**/
        self.editor.StorageManager.add('remote', {
            store(data, clb, clbErr) {

                clearTimeout(timerId);

                timerId = setTimeout(function() {
                    data['action'] = 'save_mail_template';
                    data['mail_id'] = ark_mail_cpt_config.mail_id;

                    console.log("saving!")
                    jQuery.ajax({
                        url: ark_mail_cpt_config.ajax_url,
                        type: 'POST',
                        method: 'POST',
                        cache: false,
                        data: data,
                        success : function(response) {

                            console.log(response);
                        }
                    });
                }, 2000);


                // eg. some error on remote side, store it locally
                // remote.store(data, clb, clbError);
            },

            load(keys, clb, clbErr) {
                // ...
            },
        });

        self.editor.on('storage:end:load', (resultObject) => {
            console.log("load end");
            console.log(resultObject);
            if (resultObject.hasSomeKey) {
                // do stuff
            }
        });
        // self.editor.on('storage:start:store', (objectToStore) => {
        //
        //     console.log("before storage start");
        //     console.log(objectToStore);
        // });

        self.editor.on('loop:iterations_request', async (loop) => {
            console.log('caught iterations_requst')
            var data = {};
            data['mail_id'] = ark_mail_cpt_config.mail_id;
            data['action'] = 'get_loop_iterations';

            console.log(loop);
            console.log(loop.getAttributes());
            // jQuery.ajax({
            //     url: ark_mail_cpt_config.ajax_url,
            //     type: 'POST',
            //     method: 'POST',
            //     cache: false,
            //     async: false,
            //
            //     data: data,
            //     success : function(response) {
            //
            //         loop.iterations = response.data.iterations;
            //
            //     }
            // });
        });


        self.editor.Commands.add('show_blocks', {
            run(editor, sender) {
                document.querySelector('.right_panel .blocks').style.display = 'block';
            },
            stop(editor, sender) {
                document.querySelector('.right_panel .blocks').style.display = 'none';
            },
        });
        self.editor.Commands.add('show_variables', {
            run(editor, sender) {
                document.querySelector('.right_panel .variable_overview').style.display = 'block';
            },
            stop(editor, sender) {
                document.querySelector('.right_panel .variable_overview').style.display = 'none';
            },
        });
        self.editor.Commands.add('show_styles', {
            run(editor, sender) {
                document.querySelector('.right_panel .styles').style.display = 'block';
            },
            stop(editor, sender) {
                document.querySelector('.right_panel .styles').style.display = 'none';
            },
        });



        self.editor.Commands.add('clicked_tb_l', {
            run(editor,sender, options) {
                /** The user clicked an area in the tb_l, which should result in the following:
                 *
                 * Close all categories, toggle the currently clicked category
                 **/
                let caller_wrapper = options.caller.querySelector('.collapsible-wrapper');

                let tb = options.caller.closest('.custom_tb_l')
                tb.querySelectorAll('.collapsible-wrapper').forEach(function(wrapper){
                    if(wrapper !== caller_wrapper) {
                        wrapper.classList.add('collapsed');
                    }
                });

                caller_wrapper.classList.toggle('collapsed');
            }
        });

        self.editor.Commands.add('render_image_picker', {
            run(editor, sender) {
                console.log("rendering image picker");
                jQuery("#gjs-sm-images").click();
            }
        });

        self.editor.editor.on("styleable:change:background-url", () => {

            self.component_style_change("background-url");
        })

        self.editor.on("component:update", function(component) {
            // var selected = self.editor.getSelected();

            // selected.addStyle({ 'pointer-events': 'all' });
            // selected.addStyle({ 'display': 'table' });
            // selected.addStyle({ 'width': '100%' });
            // selected.addStyle({ 'padding': '0px' });
            // var selected = self.editor.getSelected();
            // selected.setStyle({ 'pointer-events': 'all' });
            // console.log(selected);

        })



        /** Extending component types **/
        self.editor.DomComponents.addType("mj-text", {
            model: {
                defaults: {
                    resizable: {
                        tl: 0,
                        tc: 0,
                        tr: 0,
                        bl: 0,
                        bc: 1,
                        br: 0,
                        cl: 0,
                        cr: 0,
                    },
                }
            }
        });

        self.editor.DomComponents.addType("mj-column", {
            model: {
                stylable: [
                    'background-color', 'vertical-align', 'width',
                    'border-radius', 'border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius',
                    'border', 'border-width', 'border-style', 'border-color'
                ],
            },
            view: {
                styleable: [
                    'background-color', 'vertical-align', 'width',
                    'border-radius', 'border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius',
                    'border', 'border-width', 'border-style', 'border-color'
                ]
            }

        });

        self.editor.DomComponents.addType("mj-spacer", {
            model: {
                defaults: {
                    resizable: {
                        tl: 0,
                        tc: 0,
                        tr: 0,
                        bl: 0,
                        bc: 1,
                        br: 0,
                        cl: 0,
                        cr: 0,
                    },
                }
            }
        });

        self.editor.DomComponents.addType("mj-divider", {
            model: {
                defaults: {
                    resizable: {
                        tl: 0,
                        tc: 0,
                        tr: 0,
                        bl: 0,
                        bc: 0,
                        br: 0,
                        cl: 1,
                        cr: 1,
                    },
                }
            }
        });

        self.editor.DomComponents.addType("mj-button", {
            model: {
                defaults: {
                    resizable: {
                        tl: 1,
                        tc: 1,
                        tr: 1,
                        bl: 1,
                        bc: 1,
                        br: 1,
                        cl: 1,
                        cr: 1,
                    },
                }
            }
        });

        self.editor.DomComponents.addType("mj-image", {
            model: {
                defaults: {
                    resizable: {
                        tl: 1,
                        tc: 1,
                        tr: 1,
                        bl: 1,
                        bc: 1,
                        br: 1,
                        cl: 1,
                        cr: 1,
                    },
                }
            }
        });

        self.editor.on('load', () => {


            self.editor.runCommand('sw-visibility')
            /** CSS **/
            var cssComposer = self.editor.CssComposer;
            var styleManager = self.editor.StyleManager;
            // var sm = self.editor.SelectorManager;
            // var sel1 = sm.add('row');
            // var sel2 = sm.add('row-cell');
            // var rule1 = cssComposer.add([sel1]);
            // var rule2 = cssComposer.add([sel2]);
            // rule1.set('style', {
            //     width: '100%',
            //     display: 'flex',
            // })
            // // Update the style
            // rule2.set('style', {
            //     flex: 1,
            //     border: '1px solid black',
            //     margin: '5px',
            //     height: '35px'
            // });





            document.querySelector(".commands_bar .editing_tools #fullscreen").addEventListener("click",  function(e) {
                self.editor.stopCommand('core:fullscreen');
                self.editor.runCommand('core:fullscreen', {target: '#tab_contentcreation'})
            });

            document.querySelector(".commands_bar .editing_tools #save_content").addEventListener("click",  function(e) {
                self.editor.store();
            });


            /** Setting up the top commands bar **/
            document.querySelectorAll(".commands_bar .editing_tools button").forEach(function(button) {
                if(button.dataset.stateful !== undefined) {
                    button.addEventListener("click", function(e) {
                        // document.querySelector(".commands_bar #preview").classList.toggle("")
                        button.classList.toggle("btn_active");
                        if(self.editor.Commands.isActive('core:' + button.id)) {
                            self.editor.stopCommand('core:' + button.id, {force: true});
                        }
                        else {
                            self.editor.runCommand('core:' + button.id);
                        }
                    });
                }
                else {
                    button.addEventListener("click", function(e) {
                        self.editor.runCommand('core:' + button.id);
                    })
                }
            });


            self.editor.on('component:update:toolbar', function(test){
                console.log('component:update:toolbar');
                console.log(test);
            });

            self.editor.on('canvas:updateTools', function(test){
                console.log('ccanvas:updateTools');
                console.log(test);
            });

            self.editor.on('component:resize', function(test){
                console.log('component:resize');
                console.log(test);
            });

            self.editor.on('update:component:style', function(test){
                console.log('update:component:style');
                console.log(test);
            });

            self.editor.on('rteToolbarPosUpdate', function(test){
                console.log('rteToolbarPosUpdate');
                console.log(test);
            });

            self.editor.on('canvas:tools:updated', function(test){
                console.log('canvas:tools:updated');
                console.log(test);
            });

            self.editor.on('frame:scroll', function(test){
//                 console.log(test);

            })


            // self.editor.on('all', function(eventname){
            //     console.log(eventname);
            // })


            /** Changing stuff from the Plugin **/
            self.editor.Panels.removePanel('devices-c');
            /** Custom devices panel **/
            document.querySelectorAll(".commands_bar .devices button").forEach(function(button) {
                button.addEventListener("click", function(e){
                    console.log(self.editor.DeviceManager.select(button.id));
                })
            })
            self.editor.on('frame:updated', function(e){
                self.reposition_tb_l_x();

            });

            // /** Remove all Preset Blocks**/
            self.editor.BlockManager.getAll().reset();
            //
            // /** Custom blocks **/
            //
            Object.keys(self.grapes_blocks).forEach(function(block_key) {
                self.editor.BlockManager.add(block_key, self.grapes_blocks[block_key]);
            });
            document.querySelector('.blocks').appendChild(self.editor.BlockManager.render());


            /** Custom Categories **/
            const blocks = self.editor.BlockManager.getAll();
            const default_blocks = blocks.filter(block => block.attributes.attributes.class.split(' ').includes('default_block'))
            default_blocks.forEach(function(block) {

                let block_category = block.get('category');

                jQuery(block_category.view.$el).addClass("special_category");
                jQuery(block_category.view.$el).find('.gjs-title').html(ark_mail_cpt_config.editor.special_category);

                /** Get the actual HTML of the block in the Blocks div**/
                let block_class = block.attributes.attributes.class.split(' ')[0];
                jQuery("." + block_class).detach().appendTo(jQuery(block_category.view.$el).find(".default_block_container"))

                /** Clicking on the default block should not open the category container **/
                jQuery(block_category.view.$el).find(".default_block_container").on("click", function(e){
                    e.stopPropagation();
                });

                /** Set the category title **/
                jQuery(block_category.view.$el).find(".title").text(block.attributes.attributes.title);



                /** Now lastly, we check the amount of blocks we have for this category **/
                let blocks_amount = jQuery(block_category.view.$el).find('.gjs-blocks-c .gjs-block').length
                if(blocks_amount === 0) {
                    jQuery(block_category.view.$el).find('.gjs-caret-icon').remove();
                }
                else {
                    jQuery(block_category.view.$el).on("click", function(e){
                        let caret = e.target.closest(".container_title").querySelector(".gjs-caret-icon");
                        /** Check if the category is open. If it is fa-caret-right. Else, fa-caret-down **/
                        if(e.target.closest(".special_category ").classList.contains("gjs-open")) {
                            caret.classList.remove("fa-caret-right");
                            caret.classList.add("fa-caret-down");
                        }
                        else {
                            caret.classList.remove("fa-caret-down");
                            caret.classList.add("fa-caret-right");
                        }
                    })
                }


            });


            self.editor.BlockManager.getCategories().each(cat => {
                if(cat.view.el.classList.contains('special_category')) {
                    cat.set('open', false)
                }
            });

            const color_picker_stylable = ['background-color', 'container=background-color'];


            /** Toolbar customization **/

            /** Rerender the toolbar according to our will **/
            self.editor.on('canvas:tools:update', function(update) {
                if(update.type === "global") {
                    /** Update the position of the toolbar: our changes cause it to be misaligned **/
                    if(self.create_tb_l() || self.create_tb_t() || self.create_tb_r()) {
                        self.editor.Commands.get('select-comp').updateGlobalPos();
                    }

                    self.reposition_tb_l_x();
                    self.reposition_tb_y();
                    /** Do some last minute updates **/

                    /** If there is a href trait, add the components hrefbar **/
                    let selected_component = self.editor.getSelected();
                    if(selected_component !== undefined) {
                        let tb =  self.editor.Canvas.getToolbarEl();
                        let href_bar = self.editor.Canvas.getToolbarEl().querySelector('.hrefbar_input');
                        if(href_bar !== undefined) {
                            let href_trait = selected_component.getTrait('href');
                            if(href_trait !== undefined) {
                                href_bar.value = href_trait.get('value');
                            }
                        }
                    }
                }
            });

            self.create_tb_r = function() {
                let tb =  self.editor.Canvas.getToolbarEl();
                let tb_r = tb.querySelector('.custom_tb_r'); //This is the container for the Right vertical Toolbar
                let tb_r_items = tb.querySelectorAll('.gjs-toolbar > div > div:not(.custom_tb_l):not(.custom_tb_t):not(.custom_tb_r)');

                if(tb_r_items.length === 0){
                    /** Nothing to see here **/

                    return false;
                }

                /** Does the right vertical toolbar exist yet? **/
                if(tb_r === null) {
                    let items_div = tb.querySelector('div');
                    if(items_div === null) {
                        /** There simply are no toolbar contents yet, so we fight another day **/

                        return false;
                    }

                    /** Create it! **/
                    tb_r = document.createElement("div");
                    tb_r.classList.add("custom_tb_r");

                    /** Insert it !**/
                    items_div.appendChild(tb_r);
                }

                /** Now, append each item that is not in the tb_r, in the tb_r**/
                tb_r_items.forEach(function(tb_r_item) {
                    tb_r.appendChild(tb_r_item);
                });


                tb_r.style.transform = " translateX(41px)";
                tb_r.style.display = "flex";
                tb_r.style.position = "absolute";

                tb_r.style.flexDirection = "column";

                return true;

            }
            self.create_tb_l = function() {
                let tb =  self.editor.Canvas.getToolbarEl();

                let tb_l = tb.querySelector('.custom_tb_l'); //This is the container for the Right vertical Toolbar
                let tb_l_items = tb.querySelectorAll(".gjs-toolbar > div > div:not(.custom_tb_l).tb_l") //These are the items beloning there, but not yet in there
                if(tb_l_items.length === 0){
                    /** Nothing to see here **/

                    return false;
                }

                /** Does the left vertical toolbar exist yet? **/
                if(tb_l === null) {
                    let items_div = tb.querySelector('div');
                    if(items_div === null) {
                        /** There simply are no toolbar contents yet, so we fight another day **/

                        return false;
                    }

                    /** Create it! **/
                    tb_l = document.createElement("div");
                    tb_l.classList.add("custom_tb_l");

                    /** Insert it !**/
                    items_div.appendChild(tb_l);
                }

                /** Now, append each item that is not in the tb_l, in the tb_l**/
                tb_l_items.forEach(function(tb_l_item) {
                    tb_l.appendChild(tb_l_item);
                });


                /** Finally, position the toolbar correctly, to the left of the selected component **/
                self.reposition_tb_l_x(tb_l);
                return true;
            }
            self.create_tb_t = function() {
                let tb =  self.editor.Canvas.getToolbarEl();

                let tb_t = tb.querySelector('.custom_tb_t'); //This is the container for the Top Toolbar
                let tb_t_items = tb.querySelectorAll(".gjs-toolbar > div > div:not(.custom_tb_t).tb_t") //These are the items beloning there, but not yet in there
                if(tb_t_items.length === 0){
                    /** Nothing to see here **/
                    return false;
                }

                /** Does the right vertical toolbar exist yet? **/
                if(tb_t === null) {
                    let items_div = tb.querySelector('div');
                    if(items_div === null) {
                        /** There simply are no toolbar contents yet, so we fight another day **/
                        return false;
                    }

                    /** Create it! **/
                    tb_t = document.createElement("div");
                    tb_t.classList.add("custom_tb_t");

                    /** Insert it !**/
                    items_div.appendChild(tb_t);
                }

                /** Now, append each item that is not in the tb_r, in the tb_r**/
                tb_t_items.forEach(function(tb_t_item) {
                    tb_t.appendChild(tb_t_item);
                });

                // jQuery(window).trigger('resize');

                /** Finally, give it an appropriate position **/
                let tools_width = parseInt(window.getComputedStyle(self.editor.Canvas.getToolsEl()).width);   //This is the width of the parent container
                let tb_width = parseInt(window.getComputedStyle(tb_t).width);                                 //This is the width of the toolbar
                let right = (tools_width / 2) - (tb_width / 2);

                /** Place toolbar in middle of the given width **/
                tb_t.setAttribute('style', 'right: ' + right + 'px;');


                /** **/
                return true;
            }

            self.reposition_tb_y = function() {
                /** tb_l and tb_r are always the same height **/
                let toolbar_top = parseInt(self.editor.Canvas.getToolbarEl().style.top);
                let l_y = toolbar_top < 0 ? "88px" : -toolbar_top + "px";

                self.editor.Canvas.getToolbarEl().querySelector(".custom_tb_l").style.top = l_y;
                self.editor.Canvas.getToolbarEl().querySelector(".custom_tb_r").style.top = l_y;


            }
            self.reposition_tb_l_x = function(tb_l = null) {
                if(tb_l === null) {
                    let tb =  self.editor.Canvas.getToolbarEl();

                    tb_l = tb.querySelector('.custom_tb_l'); //This is the container for the Right vertical Toolbar
                }

                let selected = self.editor.getSelected();
                if(selected === undefined) {
                    return;
                }
                let element_movement = parseInt(window.getComputedStyle(selected.view.$el[0]).width);

                /** The topleft coordinate of tb_l starts in the middle of the selected elmeent's width **/

                let tb_l_width = parseInt(window.getComputedStyle(tb_l).width);

                /** It is possible this width variable is NaN. This is because on Resizing (for Desktop to Mobile), the toolbar is set to display: none
                 *  This means that it is not possible to get the width dynamically.
                 *  For this, we save the tb_l_width in the class' field
                 * **/
                if(isNaN(tb_l_width)){
                    tb_l_width = self.tb_l_width;
                }

                /** Make sure to save the current result, if we need it for the above case **/
                self.tb_l_width = tb_l_width;

                /** Add the toolbar's own width to this **/
                element_movement += parseInt(tb_l_width);
                /** Add a slight margin, so that the tb_l does not obscure the resizer tool **/
                element_movement += 10;


                /** Determine the top of the component **/
                let top = "88px;"


                tb_l.style.transform = 'translateX(-' + element_movement + 'px)';
                tb_l.style.top = top;
            }


            let componentTypes = self.editor.DomComponents.componentTypes;
            for(var i = 0; i < componentTypes.length; i++ ) {
                let component_type = componentTypes[i];

                if(component_type.id.substr(0,2) === "mj" || component_type.id === "loop") {
                    var type = self.editor.DomComponents.getType(component_type.id);
                    const typemodel = self.editor.DomComponents.getType(component_type.id).model;
                    self.editor.DomComponents.addType(component_type.id, {
                        model: {
                            initToolbar() {
                                typemodel.prototype.initToolbar.apply(this, arguments);

                                this.bg_toolbar();
                                this.traitsToolbar();
                                this.border_toolbar();
                                if(this.attributes.name === "loop") {
                                    this.loop_toolbar();
                                }
                                // if(component_type.id === 'mj-spacer') {
                                //     console.log("SPACER")
                                // }

                            },
                            border_toolbar() {
                                const tb = this.get('toolbar');
                                const classList = "tb_l border";

                                /** Create the container for all the items. This is what will be hidden or shown **/
                                let items_container = document.createElement('div');
                                items_container.classList.add('items_container');
                                items_container.classList.add('collapsible');

                                const stylable = this.attributes.stylable;
                                if(typeof(stylable) !== 'object') {
                                    return;
                                }

                                if(stylable.includes('border')) {
                                    let border_value = this.attributes.style['border']
                                    let width, width_unit,style,color;
                                    if(border_value !== undefined) {
                                        let values = border_value.split(" ");
                                        width = parseInt(values[0]); width_unit = values[0].replace(width, "");
                                        style = values[1]; color = values[2];
                                    }
                                    else {
                                        width = 0; width_unit = 'px'; style='solid'; color:'black'
                                    }
                                    this.toolbar_add_section_label("Border style", "border_style_label",  items_container);
                                    this.toolbar_add_slider(width, 'border', 'Width', items_container, width_unit);
                                    this.toolbar_add_select(style, 'border-style',['solid', 'dashed', 'dotted', 'double','groove', 'ridge', 'inset', 'outset', 'none' ], 'Style', items_container);
                                    this.toolbar_add_colorpicker(color, 'border-color', 'border-color', 'Color', items_container)
                                }

                                if(stylable.includes('border-radius')) {
                                    this.toolbar_add_section_label("Border radius", "border_radius_label",  items_container);

                                    let preset_container = document.createElement('div');
                                    preset_container.classList.add('border_radius_preset_container');

                                    let square_div = document.createElement('div'); square_div.classList.add('square_div');
                                    this.toolbar_add_button(square_div,'border_preset preset_square', preset_container);

                                    let oval_div = document.createElement('div'); oval_div.classList.add('oval_div');
                                    this.toolbar_add_button(oval_div,'border_preset preset_oval', preset_container);

                                    let circle_div = document.createElement('div'); circle_div.classList.add('circle_div');
                                    this.toolbar_add_button(circle_div,'border_preset circle', preset_container);

                                    let border_radius_value = this.attributes.style['border-radius']
                                    let tl =  tr =  br =  bl  = 0;
                                    let utl = utr = ubr = ubl = "px";

                                    if(border_radius_value !== undefined) {
                                        let values = border_radius_value.split(" ");
                                        tl  = (values[0] !== undefined)? parseInt(values[0]) : tl;
                                        utl = (values[0] !== undefined)? values[0].replace(tl, "") : utl;

                                        tr  = (values[1] !== undefined)? parseInt(values[1]) : tr;
                                        utr = (values[1] !== undefined)? values[1].replace(tr, "") : utr;

                                        br  = (values[2] !== undefined)? parseInt(values[2]) : br;
                                        ubr = (values[2] !== undefined)? values[2].replace(br, "") : ubr;

                                        bl  = (values[3] !== undefined)? parseInt(values[3]) : bl;
                                        ubl = (values[3] !== undefined)? values[3].replace(bl, "") : ubl;
                                        // width = parseInt(values[0]); width_unit = values[0].replace(width, "");
                                        //0
                                    }

                                    items_container.appendChild(preset_container);
                                    this.toolbar_add_slider(tl, 'border-radius-tl', 'Top left', items_container, utl, 0, 50);
                                    this.toolbar_add_slider(tr, 'border-radius-tr', 'Top right', items_container, utr,0, 50);
                                    this.toolbar_add_slider(br, 'border-radius-br', 'Bottom right', items_container, ubr, 0, 50);
                                    this.toolbar_add_slider(bl, 'border-radius-bl', 'Bottom left', items_container, ubl, 0, 50);


                                }

                                if(items_container.children.length > 0) {

                                    /** Create the main div for this category **/
                                    let category_div = document.createElement('div');
                                    category_div.classList.add('category_container');

                                    /** Create the title for this category. This is what we will use to hide or show the categories items **/
                                    let category_title = document.createElement('div');
                                    category_title.classList.add('category_title');
                                    category_title.innerHTML = "<span style='flex: 1; display:flex; flex-direction:column; align-items:center;'><div style='border: 2px solid #ffffffdb; width:25px; height:25px; border-radius: 2px;' ></div></span> <span style='flex: 4;margin-left: 5px;'>Shape</span> <button type='button' class='mail_composer_display_toggle'> <span class='mail_composer_toggle-indicator'></span></button>"
                                    category_div.appendChild(category_title);
                                    // category_div.appendChild(items_container);

                                    let collapsible_wrapper = document.createElement('div');
                                    collapsible_wrapper.classList.add('collapsible-wrapper');
                                    collapsible_wrapper.classList.add('collapsed');

                                    collapsible_wrapper.appendChild(items_container);
                                    category_div.appendChild(collapsible_wrapper);

                                    items_container.addEventListener('mousedown', function(e){
                                        /** Stop propagating the event. What we're doing here must remain a secret to GrapesJS' toolbar.. **/
                                        e.stopPropagation();
                                    })

                                    tb.unshift({
                                        attributes: {class : classList},
                                        command: ed => ed.runCommand('clicked_tb_l', { caller: category_div }),
                                        label: category_div,
                                    });
                                    this.set('toolbar', tb);
                                }
                            },
                            bg_toolbar() {
                                const tb = this.get('toolbar');
                                const classList = "tb_l background";

                                /** Create the container for all the items. This is what will be hidden or shown **/
                                let items_container = document.createElement('div');
                                items_container.classList.add('items_container');
                                items_container.classList.add('collapsible');

                                const stylable = this.attributes.stylable;
                                if(typeof(stylable) !== 'object') {
                                    return;
                                }

                                if(stylable.includes('background-color')) {
                                    this.toolbar_add_colorpicker(this.attributes.style['background-color'], 'background-color', 'bgcolor', "Background color", items_container)
                                }
                                if(stylable.includes('container-background-color')) {
                                    this.toolbar_add_colorpicker(this.attributes.style['container-background-color'], 'container-background-color', 'cbgcolor', "Container color", items_container)
                                }
                                if(stylable.includes('background-url')) {

                                    this.toolbar_add_imageuploader(this.attributes.style['background-url'], 'bg-url', 'background-url', 'Image', items_container );
                                }

                                if(items_container.children.length > 0) {



                                    /** Create the main div for this category **/
                                    let category_div = document.createElement('div');
                                    category_div.classList.add('category_container');

                                    /** Create the title for this category. This is what we will use to hide or show the categories items **/
                                    let category_title = document.createElement('div');
                                    category_title.classList.add('category_title');
                                    category_title.innerHTML = "<span style='flex: 1; text-align:center; font-size:25px;' class='fa fa-picture-o'></span> <span style='flex: 4;margin-left: 5px;'>Background</span> <button type='button' class='mail_composer_display_toggle'> <span class='mail_composer_toggle-indicator'></span></button>"
                                    category_div.appendChild(category_title);


                                    let collapsible_wrapper = document.createElement('div');
                                    collapsible_wrapper.classList.add('collapsible-wrapper');
                                    collapsible_wrapper.classList.add('collapsed');

                                    collapsible_wrapper.appendChild(items_container);
                                    category_div.appendChild(collapsible_wrapper);

                                    items_container.addEventListener('mousedown', function(e){
                                        /** Stop propagating the event. What we're doing here must remain a secret to GrapesJS' toolbar.. **/
                                        e.stopPropagation();
                                    })

                                    tb.unshift({
                                        attributes: {class : classList},
                                        command: ed => ed.runCommand('clicked_tb_l', { caller: category_div }),
                                        label: category_div,
                                    });
                                    this.set('toolbar', tb);
                                }
                            },

                            traitsToolbar() {
                                let traits = this.get('traits');
                                for(var i = 0; i < traits.length; i++) {
                                    let trait = traits.models[i];
                                    switch(trait.id) {
                                        case 'href':
                                            this.toolbar_add_hrefbar(this.getTrait('href'));
                                            break;
                                    }
                                }
                            },
                            toolbar_add_imageuploader(src, identification, attribute, full_name, items_container) {

                                const tbExists = items_container.querySelector("." + attribute + "_slider") !== null;

                                if(!tbExists) {
                                    let element = document.createElement('div');
                                    element.classList.add(attribute + '_imageuploader');
                                    element.classList.add('tb_l_item');

                                    let name = document.createElement("div");
                                    name.classList.add('item_name');
                                    name.innerText = full_name;


                                    let image_container = document.createElement('div');
                                    image_container.classList.add('image_input_container');

                                    let image = document.createElement('div');
                                    image.classList.add('img');
                                    image.style.backgroundImage = 'url(' + src + ')';

                                    let deletion_icon = document.createElement('span');
                                    deletion_icon.classList.add('img_delete');
                                    deletion_icon.innerText = 'x';



                                    image_container.appendChild(image);
                                    image.appendChild(deletion_icon);

                                    element.appendChild(name);
                                    element.appendChild(image_container);

                                    image_container.addEventListener('click', function(e){
                                        if(e.target.classList.contains('img')) {
                                            self.editor.runCommand('open_am_from_img', {caller: image_container});
                                        }
                                        if(e.target.classList.contains('img_delete')) {
                                            self.editor.runCommand('delete_img_from_comp')

                                        }
                                    });

                                    items_container.appendChild(element)
                                }
                            },
                            toolbar_add_button(innerHTML, identification, items_container){
                                const tbExists = items_container.querySelector(identification) !== null;
                                if(!tbExists) {
                                    let element = document.createElement('button');
                                    element.type = 'button';

                                    let class_list = identification.split(" ");
                                    for(var i = 0; i < class_list.length; i++) {
                                        element.classList.add(class_list[i]);

                                    }
                                    element.appendChild(innerHTML);

                                    element.addEventListener('click', function(e) {
                                        self.editor.runCommand('button_clicked', {caller: element})
                                    })

                                    items_container.appendChild(element);
                                }
                            },
                            toolbar_add_section_label(text, identification, items_container) {
                                const tbExists = items_container.querySelector(identification) !== null;
                                if(!tbExists) {
                                    let element = document.createElement('div');
                                    element.innerText = text;
                                    element.classList.add('tb_l_section_label');
                                    element.classList.add(identification);

                                    items_container.appendChild(element)
                                }
                            },
                            toolbar_add_select(selected, attribute, options, full_name, items_container) {
                                const tbExists = items_container.querySelector("." + attribute + "_slider") !== null;
                                if(!tbExists) {
                                    let element = document.createElement('div');
                                    element.classList.add(attribute + '_slider');
                                    element.classList.add('tb_l_item');

                                    let name = document.createElement("div");
                                    name.classList.add('item_name');
                                    name.innerText = full_name;

                                    var selectList = document.createElement("select");
                                    selectList.classList.add('select');

                                    for (var i = 0; i < options.length; i++) {
                                        var option = document.createElement("option");
                                        option.value = options[i];
                                        option.text = options[i];
                                        selectList.appendChild(option);
                                    }

                                    selectList.value = selected;

                                    element.appendChild(name);
                                    element.appendChild(selectList);


                                    selectList.addEventListener('change', function(e){
                                        self.editor.runCommand('select_change', {caller: selectList, attribute: attribute})
                                    });

                                    element.addEventListener('mousedown', function(e){
                                        /** Stop propagating the event. What we're doing here must remain a secret to GrapesJS' toolbar.. **/
                                        e.stopPropagation();
                                    });

                                    items_container.appendChild(element)
                                }
                            },
                            toolbar_add_slider(value, attribute, full_name, items_container, unit, min = 0, max = 10, ) {
                                const tbExists = items_container.querySelector("." + attribute + "_slider") !== null;

                                if(!tbExists) {
                                    let element = document.createElement('div');
                                    element.classList.add(attribute + '_slider');
                                    element.classList.add('tb_l_item');

                                    let name = document.createElement("div");
                                    name.classList.add('item_name');
                                    name.innerText = full_name;


                                    let slider = document.createElement('input');
                                    slider.classList.add('slider');
                                    slider.type = 'range';
                                    slider.value = value;
                                    slider.min = min;
                                    slider.max = max;
                                    slider.dataset.unit = unit;

                                    let slider_value = document.createElement('span');
                                    slider_value.classList.add('slider_value');
                                    slider_value.innerText = value + ' ' + unit;

                                    element.appendChild(name);
                                    element.appendChild(slider);
                                    element.appendChild(slider_value);

                                    slider.addEventListener('input', function(e){
                                        slider.nextElementSibling.innerText = slider.value + " " + slider.dataset.unit;
                                        self.editor.runCommand('slider_move', {attribute: attribute, caller: slider})

                                    });

                                    items_container.appendChild(element)
                                }
                            },
                            toolbar_add_colorpicker(color, attribute, shorthand, full_name, items_container) {
                                const tbExists = items_container.querySelector("." + shorthand + "_picker") !== null;

                                /** Adding a colorpicker **/
                                if (!tbExists) {

                                    let label = jQuery('.original_ghost').clone().removeClass("original_ghost");
                                    label.addClass(shorthand + "_picker")
                                    label.addClass('tb_l_item');

                                    jQuery(label).find("input").spectrum({
                                        color: (color !== undefined) ? color : "f00",
                                        preferredFormat: "hex",
                                        showInput: true,
                                        showPalette: true,
                                        showInitial: true,
                                        allowEmpty:true,
                                        palette: [],
                                        showAlpha: true,
                                        // showAlpha: true,
                                        // replacerClassName: 'tb-sp',
                                        move: function(tinycolor) {
                                            let color;
                                            if(tinycolor !== null) {
                                                color = tinycolor.toHexString();
                                                const _opacity = Math.round(Math.min(Math.max(tinycolor._a || 1, 0), 1) * 255);
                                                // console.log(_opacity);
                                                // console.log( _opacity.toString(16).toUpperCase());
                                                color =  color + _opacity.toString(16).toUpperCase();
                                            }
                                            else {
                                                color = null;
                                            }

                                            // console.log(color);


                                            self.editor.runCommand('colorpicker_move', {color: color, attribute: attribute})
                                        }
                                    });

                                    let name = document.createElement("div");
                                    name.classList.add('item_name');
                                    name.innerText = full_name;

                                    label[0].insertBefore(name, label[0].querySelector("input"));
                                    items_container.appendChild(label[0])

                                    // label[0].appendChild(name)
                                }
                                return null;
                            },
                            toolbar_add_background_img_ui() {
                                /** Add a ui for background images **/
                                const tb = this.get('toolbar');
                                const classlist = 'tb_l tb_bgimg_ui';

                                let ui_container = document.createElement('div');
                                // ui_container.add('bgimg_ui');


                                // background-position	percent / 'left','top',... (2 values max)	css background position (see outlook limitations below)	top center
                                // background-position-x	percent / keyword	css background position x	none
                                // background-position-y	percent / keyword	css background position y	none
                                // background-repeat	string	css background repeat	repeat
                                // background-size	px/percent/'cover'/'contain'	css background size	auto
                                // background-url

                                return null;

                            },
                            toolbar_add_hrefbar(trait) {
                                const tb = this.get('toolbar');
                                const classlist = 'tb_t tb_hrefbar';

                                let hrefbar = document.createElement('div');
                                hrefbar.classList.add('hrefbar_container');
                                hrefbar.setAttribute('style', 'display: flex;');

                                let href_input = document.createElement('input');
                                href_input.classList.add('hrefbar_input');
                                href_input.name = "hrefbar";
                                href_input.placeholder = "Insert link or variable";
                                href_input.contentEditable = true;
                                href_input.setAttribute('style', 'resize: none; overflow: hidden;');

                                let apply_btn = document.createElement('button');
                                apply_btn.classList.add('tb_apply_btn');
                                apply_btn.setAttribute('type', 'button');
                                apply_btn.innerText = "Apply";

                                hrefbar.appendChild(href_input);
                                hrefbar.appendChild(apply_btn);

                                tb.unshift({
                                    attributes: {class : classlist},
                                    label: hrefbar,
                                    command: ed => ed.runCommand('apply_href', { caller: hrefbar }),
                                });
                                this.set('toolbar', tb);

                                href_input.addEventListener("input", function(e) {
                                    self.editor.runCommand('apply_href', { caller: hrefbar });
                                    // let caret_pos = href_input.value.selectionStart;
                                    // let test_string = href_input.value.substring(0, caret_pos);
                                    // let opening_pos = test_string.lastIndexOf("{");
                                    // let close_pos = test_string.lastIndexOf("}");
                                    // if(close_pos === -1 && close_pos < opening_pos){
                                    //     //autocomplete
                                    //     //render suggestions
                                    //     //suggestion_field visible
                                    //     console.log("suggestion!")
                                    // }
                                    // else {
                                    //     console.log("no suggestion");
                                    // }

                                    // jQuery(href_input).autocomplete("search", href_input.value);

                                });


                                var var_autocomplete = jQuery(href_input).autocomplete({
                                    minLength: 2,
                                    source: amcpt.var_suggestion_autocomplete_source,
                                    response: function(event, ui) {
                                        console.log(ui);
                                        // jQuery(panel.parts.content.$).find('#variable_suggestions_panel').html(amcpt.render_variable_suggestions(ui.content));
                                    },
                                    open : function(event, ui) {
                                        //Hide the standard Autocomplete dropdown
                                        jQuery(".ui-autocomplete").hide();
                                    },
                                });
                            },
                            loop_toolbar() {
                                // coreMjmlModel.initToolbar.apply(this, arguments);
                                const tb = this.get('toolbar');
                                const tbExists = tb.some(item => item.command === 'render_colorpicker');

                                if (!tbExists) {

                                    let loop = this;
                                    let pack = {
                                        'component' : this,
                                        'set_ref_full_name' : '',
                                        'data_refs' : [],
                                        'vars' : []
                                    };
                                    let label = document.createElement('div');
                                    self.editor.trigger('loop_tb_data_request', pack);

                                    label.setAttribute('style', 'background-color:#2271B1; padding:5px; box-shadow: 0 8px 25px -4px rgb(0 0 0 / 60%); display:flex; flex-direction:column ');
                                    label.setAttribute('class', 'loop_tb_label');
                                    label.innerHTML = this.loop_data_package_html(pack);

                                    console.log("init toolbar");
                                    tb.unshift({
                                        attributes: {class : 'tb_l tb-loop_config', style:'background-color:transparent; transition: transform 0.1s;'},
                                        command: function(loop) {
                                            // console.log(test);
                                            self.editor.trigger('loop_tb_click', loop);
                                        },
                                        // command: ed => ed.runCommand('render_colorpicker', { caller: label }),
                                        label: label,
                                    });
                                    this.set('toolbar', tb);
                                    var that = this;
                                    label.querySelector(".head").addEventListener("click", this.loop_toggle_toolbar_menu);
                                    label.querySelector(".body").addEventListener("click", function(e) {
                                        that.loop_handle_toolbar_body_click(e);
                                    });
                                }

                            },
                            loop_data_package_html(data_package) {
                                /** Creates the HTML for the toolbar button
                                 * data_package:
                                 *    'component' : this
                                 *    'set_ref_full_name' : A User friendly version of the Data reference, for the main label
                                 *    'data_refs' : Other data references the user can apply to the Loop component
                                 *    'vars' : The variables of the applied data ref
                                 *
                                 * **/

                                let head = 'Apply a dataset';
                                /** Determine the Header of the button **/
                                if(data_package['set_ref_full_name'] !== '') {
                                    head = "Loop: " + data_package['set_ref_full_name'];
                                }


                                let data_refs = '';
                                console.log(data_package);
                                if(data_package.data_refs.length > 0) {
                                    data_package.data_refs.forEach(function(data_ref){
                                        let hp_length = data_ref['hierarchy_path'].length - 1;
                                        let name = data_ref['name'] !== undefined ? data_ref['name'] : data_ref['hierarchy_path'][hp_length];
                                        let dataset_data_ref = JSON.stringify(data_ref['hierarchy_path']);

                                        data_refs += '<div class="tb_data_ref" data-data_ref=\'' + encodeURIComponent(dataset_data_ref) + '\'>' + name + '</div>';
                                    });
                                }
                                else {
                                    data_refs = 'No loopable datasets available for application';
                                }


                                // let vars = '';
                                // if(data_package.vars.length > 0) {
                                //
                                // }
                                // else {
                                //   vars = 'No variables available for this dataset';
                                // }



                                return '<div class="head"><i class="fa fa-cog"></i> <span class="head_label">' + head + '</span></div>' +
                                    '<div class="body"  style="display:flex; flex-direction:column">' +
                                    // '<div>Variable set</div>' +
                                    // '<div class="dataset_variables" style="display:block"> ' + vars + '</div>' +
                                    '<div> Available sets</div>' +
                                    '<div class="available_sets" style="display:block">' + data_refs + '</div>' +
                                    '</div>';
                            },
                            loop_handle_toolbar_body_click(e) {
                                let target = e.srcElement;
                                if(target.classList.contains("tb_data_ref")) {

                                    let loop_tb_label = target.closest(".loop_tb_label");
                                    let width = parseInt(getComputedStyle(loop_tb_label).width);
                                    let horizontalOffset = -500 - width;

                                    // this.el.setAttribute('style', `${this.el.getAttribute('style') + this.attributes.style}`);
                                    let data_ref = target.dataset.data_ref;
                                    // this.setAttribute('data_ref', {data_ref});
                                    this.addAttributes({'data_ref': data_ref});
                                    this.view.render();

                                    loop_tb_label.querySelector(".head .head_label").innerText = "Loop: " + target.innerText;

                                    /** recompute label's position **/
                                    width = parseInt(getComputedStyle(loop_tb_label).width);
                                    horizontalOffset = -500 - width;

                                    // loop_tb_label.parentElement.style.transform = 'translate('+ horizontalOffset + 'px, 10px)';
                                }
                            },
                            /**
                             * Toggles the visiblity of the toolbar menu
                             * @param element - the header of the toolbar menu
                             */
                            loop_toggle_toolbar_menu(e) {
                                console.log(e);
                                let parent = e.srcElement.closest(".loop_tb_label");
                                let target = parent.querySelector(".body");
                                target.classList.toggle("visible");

                            }

                        },
                        // Double click on link open link editor
                        view: type.view.extend({
                            // events: {
                            //     click: "hide_colorpicker",
                            // },
                            // hide_colorpicker: function(e) {
                            //     console.log(e);
                            //     console.log(e.target);
                            //     console.log("found container: " + e.target.closest(".sp-container"));
                            //
                            //     if(e.target.closest(".sp-container") === null) {
                            //
                            //         jQuery(".sp-active").each(function(index, element) {
                            //             jQuery(element).removeClass("sp-active");
                            //             element.click();
                            //         })
                            //     }
                            // }

                        })
                    });

                }
            }

            self.editor.Commands.add('open_am_from_img', {
                run(editor, sender, options) {
                    editor.AssetManager.open({
                        select(asset, complete) {
                            const selected = editor.getSelected();

                            /** Set the url on the selected element **/
                            let style_obj = {};
                            style_obj['background-url'] = asset.getSrc();
                            selected.addStyle(style_obj);

                            /** Set the url on the toolbar element **/
                            options.caller.querySelector(".img").style.backgroundImage = 'url(' + asset.getSrc() + ')';

                            complete && am.close();
                        }
                    });
                }
            });
            self.editor.Commands.add('button_clicked', {
                run(editor, sender, options) {
                    let selected = self.editor.getSelected();
                    let clicked = options.caller;

                    if(clicked.classList.contains('border_preset')) {
                        let style = "";
                        switch(clicked.children[0].className){
                            case 'square_div':
                                style = "0px 0px 0px 0px";
                                break;
                            case 'oval_div':
                                style = "6px 6px 6px 6px";
                                break;
                            case 'circle_div':
                                style = "50% 50% 50% 50%";
                                break;
                        }
                        /** Update the selected component's toolbar **/
                        let values = style.split(" ");
                        let tb = self.editor.getSelected().get('toolbar').find(element => element.attributes.class === "tb_l border")


                        for(var i = 0; i < values.length; i++) {
                            let slider_div;
                            switch(i) {
                                case 0:
                                    slider_div = tb.label.querySelector('.border-radius-tl_slider');
                                    break;
                                case 1:
                                    slider_div = tb.label.querySelector('.border-radius-tr_slider');
                                    break;
                                case 2:
                                    slider_div = tb.label.querySelector('.border-radius-br_slider');
                                    break;
                                case 3:
                                    slider_div = tb.label.querySelector('.border-radius-bl_slider');
                                    break;
                            }
                            let value = parseInt(values[i]);
                            let unit =  values[i].replace(value, "");

                            let input = slider_div.querySelector('input');
                            let slider_value = slider_div.querySelector('.slider_value');
                            slider_value.innerHTML = values[i];
                            input.dataset.unit = unit;
                            input.value = value;
                        }

                        let style_obj = {};
                        style_obj['border-radius'] = style;
                        selected.addStyle(style_obj);

                        /** Make up for bug in Grapes: make sure that view's border is also updated **/
                        selected.getView().$el[0].style.borderRadius = "0px 0px 0px 0px";
                    }
                }
            })
            self.editor.Commands.add('select_change', {
                run(editor, sender, options) {
                    let selected = self.editor.getSelected();
                    let style = selected.attributes.style[options.attribute];
                    let slider = options.caller;

                    if(options.attribute === 'border-style') {
                        options.attribute = 'border';
                        style = selected.attributes.style[options.attribute];

                        if(style !== undefined) {
                            style = style.split(" ");
                            style[1] = options.caller.value;
                            style = style.join(" ");
                        }
                        else {
                            style = "1px " + options.caller.value + " black";
                        }
                    }
                    else {
                        style = options.caller.value;
                    }

                    let style_obj = {};
                    style_obj[options.attribute] = style;
                    selected.addStyle(style_obj);
                }
            })
            self.editor.Commands.add('colorpicker_move', {
                run(editor, sender, options) {
                    var selected = self.editor.getSelected();
                    let style = selected.attributes.style[options.attribute];


                    /** Determine the style to change **/
                    if(options.attribute === "border-color") {

                        if(options.color === null) {
                            selected.removeStyle('border');
                            return;
                        }

                        options.attribute = 'border';
                        style = selected.attributes.style[options.attribute];

                        if(style !== undefined) {
                            style = style.split(" ");
                            style[2] = options.color;
                            style = style.join(" ");
                        }
                        else {
                            style = "1px solid " + options.color;
                        }
                    }
                    else {
                        if(options.color === null) {
                            selected.removeStyle(options.attribute);
                            return;
                        }

                        style = options.color;
                    }
                    let style_obj = {};
                    style_obj[options.attribute] = style
                    selected.addStyle(style_obj);
                }
            })
            self.editor.Commands.add('slider_move', {
                run(editor, sender, options) {

                    let selected = self.editor.getSelected();
                    let style = selected.attributes.style[options.attribute];
                    let slider = options.caller;


                    if(options.attribute === 'border') {
                        if(style !== undefined) {
                            style = style.split(" ");
                            style[0] = slider.value + slider.dataset.unit;
                            style = style.join(" ");
                        }
                        else {
                            style = slider.value + slider.dataset.unit + " solid black";
                        }
                    }
                    else if(options.attribute.includes("border-radius")) {
                        let position = options.attribute.replace("border-radius-", "");
                        options.attribute = "border-radius";

                        style = selected.attributes.style['border-radius'];
                        style = style === undefined ? [] : style.split(" ");

                        switch(position) {
                            case 'tl':
                                style[0] = slider.value + slider.dataset.unit;
                                break;
                            case 'tr':
                                style[0] === undefined ? "1px" : style[0];
                                style[1] = slider.value + slider.dataset.unit;
                                break;
                            case 'br':
                                style[0] === undefined ? "1px" : style[0];
                                style[1] === undefined ? "1px" : style[1];
                                style[2] = slider.value + slider.dataset.unit;
                                break;
                            case 'bl':
                                style[0] === undefined ? "1px" : style[0];
                                style[1] === undefined ? "1px" : style[1];
                                style[2] === undefined ? "1px" : style[2];
                                style[3] = slider.value + slider.dataset.unit;
                                break;
                            //tl is first.
                            //if tr, tl might not be set. check and set both
                            //if br, tr and tl might not be set, but must be
                            //if bl, tr, tl and br might not be set, but must be
                        }

                        style = style.join(" ");
                    }

                    else {
                        style = slider.value + slider.dataset.unit;
                    }

                    let style_obj = {};
                    style_obj[options.attribute] = style;
                    selected.addStyle(style_obj);

                    /** Make up for bug in Grapes: make sure that view's border is also updated **/
                    selected.getView().$el[0].style.borderRadius = "0px 0px 0px 0px";
                }
            })

            self.editor.Commands.add('apply_href', {
                run(editor,sender, options) {
                    console.log('apply href');
                    let href_input = options.caller.querySelector('input');
                    href_input.focus();
                    self.editor.getSelected().getTrait('href').set('value', href_input.value);

                }
            })


            /** We need to return all variables we find in a string, including their start and end indices **/
            self.string_find_all_vars = function(full_string) {
                let candidates = full_string.split("{");

                let op_index = full_string.lastIndexOf("{");
                let var_candidate = full_string.substring(op_index);


            }

            /** Hiding the colorpicker **/

            /** editor iframe **/
            jQuery(".gjs-frame")[0].contentWindow.document.querySelector("body > div").addEventListener("click", function(e) {

                var active = document.querySelector(".sp-active");
                if(active !== null) {
                    active.classList.remove("sp-active");
                    active.click();

                }
            });

            /** Rest of the doc **/
            document.addEventListener("click", function(e) {
                var active = document.querySelector(".sp-active");
                if(active !== null && e.target.closest(".sp-container") === undefined) {
                    active.classList.remove("sp-active");
                    active.click();
                }
            })

            let components = ark_mail_cpt_config.editor.components !== "[]" ? JSON.parse(ark_mail_cpt_config.editor.components) : ark_mail_cpt_config.editor.html;
            self.editor.setComponents(components);
        });



        self.editor.on("styleable:change:background-color", () => {
            // console.log("BACKGROUNDDD")
            //     // var selected = self.editor.getSelected();
            //     // selected.addStyle({ 'pointer-events': 'all' });
            //     // selected.addStyle({ 'display': 'table' });
            //     // selected.addStyle({ 'width': '100%' });
            //     // selected.addStyle({ 'padding': '0px' });
        })

        /** On select **/
        self.editor.on("component:selected", function(component) {
            // var stylable = component.get('stylable');
            // console.log(stylable);
            // if(!stylable.includes("height")) {
            //     stylable.push("height");
            //     component.set({'stylable': stylable});
            // }

            // var attributes = component.get('attributes');
            //
            // console.log(attributes);
            // console.log(component.getName());
            // console.log(component.is('mj-body'));
            //
            // if(component.getName() !== "Body") {
            //     component.set("resizable", true);
            //     component.setDragMode('absolute')
            // }

            if(component.getName() === "loop") {
                /** Load the HTML in the toolbar! **/
                console.log("loading the Loop toolbar html")
            }
        });

        /** Clicking the Loop config button **/
        self.editor.on('loop_tb_click', function(e){
            console.log("got the click")

            console.log(e);
            e['test'] = "SOMETHING";

        })


        /** return the package
         *
         * let pack = {
         *   'component' : this,            //The Loop componenet
         *   'set_ref_full_name' : '',      //The dataset currently applied to the Loop
         *   'vars' : []                    //The variables belonging to the current dataset
         *   'data_refs' : [],              //All other possile ToMany datasets
         * };
         * **/
        self.editor.on('loop_tb_data_request', function(e){
            let data_ref = e['component'].getAttributes()['data_ref'];
            if(data_ref === undefined) {data_ref = e['component'].getAttributes()['hierarchy_path']}


            data_ref = JSON.parse(decodeURIComponent(data_ref));
            let dataset = (data_ref !== 'null' && data_ref !== null)? self.get_dataset_by_hierarchy(self.datalinks, data_ref) : null;

            e['set_ref_full_name'] = dataset !== null? dataset['desc'] : '';
            e['data_refs'] = self.get_all_toMany_local();

            console.log(e);
        });





    }



    self.get_dataset_by_hierarchy = function(datalink, data_ref){
        if(data_ref.length === 0) {
            return datalink;
        }

        if(datalink.links[data_ref[0]] !== undefined) {
            var next_datalink = datalink.links[data_ref[0]];
            data_ref.shift();
            return self.get_dataset_by_hierarchy(next_datalink, data_ref);
        }
        else {
            return  undefined;
        }

    }


    /**
     * Get all the toMany datasets
     */
    self.get_all_toMany_local = function() {

        var toMany = []
        Object.keys(self.datalinks.links).forEach(function(key){
            self.get_all_toMany_local_rec(self.datalinks.links[key], toMany, [key]);
        })

        return toMany;
    }

    self.get_all_toMany_local_rec = function(datalink, toMany, hierarchy_path) {
        if(datalink.many) {

            toMany.push({'name' : datalink.desc, 'hierarchy_path' : hierarchy_path});
        }
        Object.keys(datalink.links).forEach(function(link_key){
            let path = [...hierarchy_path];
            path.push(link_key);
            self.get_all_toMany_local_rec(datalink.links[link_key], toMany, path)
        });
    }


    // /**
    //  * Get the variable set of a datalink
    //  * @param hierarchy_path
    //  */
    // self.get_variable_set = function(hierarchy_path) {
    //     var data = {};
    //     data['mail_id'] = ark_mail_cpt_config.mail_id;
    //
    //
    //     hierarchy_path = [
    //         "wc_booking",
    //         "wp_users",
    //         "wp_comments"
    //     ];
    //
    //     if(hierarchy_path !== "null") {
    //         data['hierarchy_path'] = hierarchy_path;   //wcpv_product_vendors
    //
    //         data['action'] = "get_example_variable_set";
    //
    //
    //         jQuery.ajax({
    //             url: ark_mail_cpt_config.ajax_url,
    //             type: 'POST',
    //             method: 'POST',
    //             cache: false,
    //             data: data,
    //             success : function(response) {
    //
    //                 return response.data.variable_set;
    //             }
    //         });
    //     }
    //
    //     return [];
    // }
    //
    //

    self.get_loop_tb_package = function(pack) {
        console.log("getting loop package");
        console.log(pack);

        // let pack = {
        //     'component' : this,
        //     'set_ref_full_name' : '',
        //     'data_refs' : [],
        //     'vars' : []
        // };
    }


    /** Navbar **/
    self.navigation = function(e) {
        //Set the current active tab to inactive
        let target = e.target.closest(".nav-item");
        document.querySelector(".mail_composer_tab.tab_active").classList.toggle("tab_active")
        document.querySelector('#' + target.dataset.target).classList.toggle("tab_active");

        //Same vor the navbar items
        document.querySelector(".nav-item.nav-item_active").classList.toggle("nav-item_active");
        target.classList.toggle("nav-item_active");

    }

    /** Datalinks tab **/

    self.keyup_delegation = function(e) {
        if(e.target.classList.contains("format_searcher_input")) {
            switch(e.keyCode) {
                case 13:
                    /** User has pressed Enter on a new format option. So, we create a new node, and append it tot the format options  **/
                    self.add_format_function(e);

                    // data-arguments='<?php echo json_encode(array("offset"=> 3, "length" => 8))
                    break;
            }
        }
    }

    self.input_delegation = function(e) {
        console.log(e.target);
        console.log(e.target.value)

        if(e.target.classList.contains("format_searcher_input")) {
            self.suggest_format_functions(e)
        }
    }


                                                                /** Errorlog tab **/
    self.initialize_errorlog_tab = function() {
        var errorlog_tab = document.querySelector("#tab_errorlog");
        var errorlog_nav = document.querySelector("#nav-item_errorlog");

        var id_errors_el = document.querySelector("#errorlog_id_errors");
        var render_errors_el = document.querySelector("#errorlog_render_errors");


        if(id_errors_el !== null) {
            id_errors_el.addEventListener("click", function(e) {
                if(e.target.classList.contains("btn_send_rectification")) {

                    self.submit_id_rectification_form(
                        self.gather_id_rectification_form(e.target.closest(".rectification_form"), "id")
                    );
                }
            });

            id_errors_el.addEventListener("input", function(e){
                if(e.target.classList.contains("formfield_invalid")) {
                    e.target.classList.remove("formfield_invalid");
                }
                if(e.target.classList.contains("checkbox_check_all")) {
                    /** Check all checkboxes**/
                    e.target.closest(".rectification_form_mailing_list").querySelectorAll(".rectification_recipient_list input").forEach(function(checkbox) {
                        checkbox.checked = e.target.checked;
                    });
                }
            });
        }

        if(render_errors_el !== null) {
            render_errors_el.addEventListener("click", function(e) {
                if(e.target.classList.contains("btn_send_rectification")) {
                    self.submit_id_rectification_form(
                        self.gather_id_rectification_form(e.target.closest(".rectification_form"), "render")
                    )
                }
            });

            render_errors_el.addEventListener("input", function(e){
                if(e.target.classList.contains("checkbox_check_all")) {
                    /** Check all checkboxes**/
                    e.target.closest(".rectification_form_mailing_list").querySelectorAll(".rectification_recipient_list input").forEach(function(checkbox) {
                        checkbox.checked = e.target.checked;
                    });
                }
            });
        }

        /** Pagination **/
        errorlog_tab.querySelectorAll(".paginator").forEach(function(paginator){
            let btn_prev = paginator.querySelector('.btn_errorlog_back');
            let btn_next = paginator.querySelector('.btn_errorlog_next');
            let btn_numbered = paginator.querySelectorAll('.btn_errorlog_numbered')

            btn_prev.addEventListener("click", function(e) {
                let btn_active = paginator.querySelector('.btn_errorlog_pag_active');
                let index_active = parseInt(btn_active.innerHTML.trim());

                if(index_active > 1) {
                    let new_active = index_active - 1;
                    self.pagination_btn_click(paginator, btn_numbered[new_active - 1],paginator.dataset.error_template + new_active);
                }
            });

            btn_next.addEventListener("click", function(e) {
                let btn_active = paginator.querySelector('.btn_errorlog_pag_active');
                let index_active = parseInt(btn_active.innerHTML.trim());

                if(index_active < parseInt(btn_numbered[btn_numbered.length-1].innerHTML.trim())) {
                    let new_active = index_active + 1;
                    self.pagination_btn_click(paginator, btn_numbered[new_active - 1], paginator.dataset.error_template + new_active);
                }
            });

            btn_numbered.forEach(function(btn, index) {
                btn.addEventListener("click", function(e) {
                    let btn_active = paginator.querySelector('.btn_errorlog_pag_active');
                    let index_active = parseInt(btn_active.innerHTML.trim());
                    if(btn !== btn_active) {
                        self.pagination_btn_click(paginator,btn, btn.dataset.target)
                    }
                });
            });
        });
    }

    self.pagination_btn_click = function(paginator, btn, new_index) {
        console.log(new_index);

        /** Switch active pagination number **/
        paginator.querySelector(".btn_errorlog_pag_active").classList.remove("btn_errorlog_pag_active");
        btn.classList.add("btn_errorlog_pag_active");

        /** Switch active errorlog **/
        let errorlog_container = paginator.closest(".errorlog_errors_container");
        errorlog_container.querySelector(".active_errorlog").classList.remove("active_errorlog");
        errorlog_container.querySelector("#" + new_index).classList.add("active_errorlog");

    }

    /** Gathers the data for the ID Rectification form, and submits it it **/
    self.gather_id_rectification_form = function(form, error_kind) {
        let valid = true;
        let formData = {};

        /** First, the id values **/
        let ids = {};
        form.querySelectorAll(".rectification_form_id_fields input").forEach(function(input){
            if(!input.checkValidity()) {
                input.classList.add("formfield_invalid");
                alert("Invalid ID for " + input.name);
                valid = false;
                return;
            }
            ids[input.name] = input.value
        })

        if(!valid) {  return; }

        /** Next, the recipients **/
        let recipients = [];
        form.querySelectorAll(".rectification_form_mailing_list input").forEach(function(input, index) {
            if(index !== 0 && input.checked) {
                recipients.push(input.value);
            }
        });

        if(recipients.length === 0) {
            alert("No recipients selected");
            valid = false;
        }

        if(!valid) { return; }

        formData['ids'] = ids;
        formData['recipients'] = recipients;
        formData['error_index'] = parseInt(document.querySelector(".btn_errorlog_pag_active").innerHTML) - 1;
        formData['error_kind'] = error_kind;


        return formData;
    }

    self.submit_id_rectification_form = function(formdata) {
        formdata['action'] = 'mailcat_rectify_id_error';
        formdata['mail_id'] = ark_mail_cpt_config.mail_id;


        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: formdata,
            success : function(response) {
                if(response['success']) {
                    alert(response['data']['msg']);
                    document.querySelector("#tab_errorlog").innerHTML = response['data']['errortab_html'];
                    jQuery('#badge_error_count').replaceWith(response['data']['errortab_badge']);
                    self.initialize_errorlog_tab();
                }
                else {
                    alert(response['data']['msg']);
                }
            }
        });
    }


                                                                /** END Errorlog tab **/


                                                                /** DATALINK TAB **/



    /** Selector used in the Add Datalink Dialog
     *
     * Must append the chosen type to the Hierarchy Path
     * Must render the preview of the variable sets
     * **/
    self.handle_change_primary_selection = function(e) {

        let selected = e.target.selectedOptions[0];
        document.querySelector("#dialog_add_datalink_hierarchy_path #hierarchy_placeholder").innerText = selected.innerText;


        self.render_secondary_selection_form(selected);

        // self.view_variable_sets(
        //     value['link_type'] ,
        //     value['link_name'],
        //     "dialog_add_datalink_variable_sets",
        //     document.querySelector("#hidden_parent_link_type").value );

    }

    /**
     * User changed one of the secondary forms, meaning we need to rerender the dataset
     * @param e - The change event, unused
     */
    self.handle_secondary_selection = function(e) {

        /** Get all data from the form This takes care of the secondary forms **/
        let form = document.querySelector('#dialog_add_datalink');
        let formData = new FormData(form);

        /** There is a .dataset in the primary selection options, we need to manually fetch that **/
        let selected_primary = document.querySelector("#dialog_add_datalink_type_selector").selectedOptions[0];
        for(key in selected_primary.dataset) {
            formData.append('link_spec[' + key + ']', selected_primary.dataset[key]);
        }

        /** Finally, we need to get the current hierarchy path **/
        let hierarchy_path = [];
        document.querySelectorAll("#dialog_add_datalink_hierarchy_path div:not(:last-child)").forEach(function(node) {
            hierarchy_path.push(node.innerText);
        })
        formData.append('hierarchy_path', hierarchy_path);

        for (let [key, value] of formData.entries()) {
            console.log(key + " = " +  value);
        }

        /** Render the new variable set **/

        self.render_dataset(formData, "dialog_add_datalink_variable_sets", false);
    }


    /**
     *
     * @param formData          formData is a FormData object containing the link_type and link_spec
     * @param view_id           view_id is the id of the HTML element in which we want to render our result
     * @param show_formatting   boolean indicating whether we want to render the user-saved variable formatting
     */
    self.render_dataset = function(formData, view_id, show_formatting) {

        formData.append('action', 'render_dataset');
        formData.append('show_formatting', show_formatting )
        formData.append('mail_id', ark_mail_cpt_config.mail_id);


        // for (let [key, value] of link_spec.entries()) {
        //     data[key]  =   value;
        // }
        // console.log(data)

        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,

            data: formData ,
            contentType: false,
            processData: false,
            success : function(response) {
                document.querySelector("#" + view_id).innerHTML = response.data.html;
            }
        });
    }


    self.render_secondary_selection_form = function(selected_primary) {

        let hierarchy_path = [];
        document.querySelectorAll("#dialog_add_datalink_hierarchy_path div:not(:last-child)").forEach(function(node) {
            hierarchy_path.push(node.innerText);
        })

        let data = {
            'hierarchy_path' : hierarchy_path,
            'action' : 'render_secondary_selection_form',
            'link_type' : selected_primary.value,
            'link_spec' : {}
        };

        for(key in selected_primary.dataset) {
            if(key === "taxonomies") {
                data['link_spec'][key] = JSON.parse(selected_primary.dataset[key]);
            }
            else {
                data['link_spec'][key] = selected_primary.dataset[key];
            }
        }

        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: data,
            success : function(response) {
                document.querySelector("#dialog_add_datalink_secondary_container").innerHTML = response.data.html;
                console.log(response);
            }
        });
    }

    self.change_delegation = function(e) {
        if(e.target.id === "dialog_add_datalink_type_selector") {
            /** The type selector is the primary selection input. From this, we get the secondary selection inputs **/
            self.handle_change_primary_selection(e);
        }

        else {
            self.handle_secondary_selection(e);
        }
    }

    self.click_delegation = function(e) {
        console.log(e.target.classList)

        if(e.target.classList.contains("datalink_btn")) {
            console.log("heeey")
        }
        if(e.target.classList.contains('variable_set_title') || e.target.classList.contains('mail_composer_toggle-indicator')) {
            self.toggle_variableset_visibility(e.target.closest(".variable_set_title"))
        }

        else if(e.target.classList.contains('datalink_name') || e.target.classList.contains('btn_view_variable_sets') || e.target.classList.contains('datalink_btn_vars')) {
            var datalink_row = e.target.closest(".datalink_row");
            var datalink_row_parent = self.get_datalink_parent(datalink_row);

            self.view_variable_sets(
                datalink_row.dataset.link_type,
                datalink_row.dataset.link_name,
                "variable_sets",
                datalink_row_parent !== null ? datalink_row_parent.dataset.link_type : null,
                datalink_row
            );

            /** Set all other datalink rows as inactive **/
            document.querySelectorAll(".datalink_row.active").forEach(function(active_row) {
                active_row.classList.remove("active");
            });

            /** Set the clicked row as active **/
            e.target.closest(".datalink_row").classList.add("active");
        }

        else if(e.target.classList.contains('datalink_btn_delete') || e.target.classList.contains('btn_delete_datalink')) {
            var path = [];
            self.get_datalink_hierarchy_path(path, e.target.closest(".datalink_row"));
            self.delete_datalink(path);
        }

        else if(e.target.classList.contains("datalink_btn_add")) {
            self.setup_add_datalink_dialog(e);
        }

        else if (e.target.classList.contains("new_root_datalink")) {
            self.setup_add_datalink_dialog(e);
        }
        else if(e.target.classList.contains("submit_new_hierarchy")) {
            var path = [];
            document.querySelectorAll("#dialog_add_datalink_hierarchy_path div:not(:last-child)").forEach(function(node) {
                path.push(node.innerText);
            })
            var link_select = JSON.parse(document.querySelector("#dialog_add_datalink_type_selector").value)
            var description = document.querySelector("#dialog_add_datalink_description").value;
            var many = (link_select['many'] !== undefined);


            self.new_datalink(path, link_select['link_type'], link_select['link_name'], many, description);

        }

        else if (e.target.classList.contains("value")) {
            self.save_format_list(e.target.closest(".datalink_variable"));
        }

        else if (e.target.classList.contains("function_description") || e.target.classList.contains("format_function_option") || e.target.classList.contains("function_title")) {

            var clicked_element = e.target.closest(".format_function_option");
            var format_option_adder =  e.target.closest(".format_option_adder");
            format_option_adder.querySelector(".format_searcher_input").value = clicked_element.querySelector(".function_title").innerText;
        }

        else if (e.target.classList.contains("remove_func")) {
            self.remove_format_function(e.target.closest(".format_func_container"));
        }


    }


    self.setup_add_datalink_dialog = function(e) {
        var path = [];
        var datalink_row = e.target.closest(".datalink_row");
        if(datalink_row !== null) {
            self.get_datalink_hierarchy_path(path, e.target.closest(".datalink_row"));

            //Add the parent's link_type of the current datalink_row to the hidden input's value
            document.querySelector("#hidden_parent_link_type").value = datalink_row.dataset.link_type;
        }

        var add_dialog_hierarchy_path = document.querySelector("#dialog_add_datalink_hierarchy_path");
        add_dialog_hierarchy_path.innerHTML = "";

        path.forEach(function(link_name) {
            var node = document.createElement("div");
            node.innerText = link_name;
            add_dialog_hierarchy_path.append(node)
        });

        //Add a placeholder node, for the link type that will be chosen by the user in the dialog
        var placeholder_node = document.createElement( "div");
        placeholder_node.id = "hierarchy_placeholder";
        placeholder_node.innerText = "__";
        add_dialog_hierarchy_path.append(placeholder_node)




        //Add an input to the hierarchy dialog for each path element in the hierarchy
        self.get_possible_datalinks(datalink_row);
    }

    /** For any part in the hierarchy, we must determine which links are possible to be made. **/
    self.get_possible_datalinks = function(datalink_row) {

        var link_name = null;
        var link_type = null;
        if(datalink_row !== null) {
            link_name = datalink_row.dataset.link_name;
            link_type = datalink_row.dataset.link_type;
        }

        var data = {};
        data['mail_id'] = ark_mail_cpt_config.mail_id;
        data['link_name'] = link_name;
        data['link_type'] = link_type;
        data['action'] = "render_primary_datalink_form";

        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: data,
            success : function(response) {

                /** Rerender the select element **/
                // var temp = document.createElement("div");
                // console.log(response.data);
                // console.log(response.data.html_form_primary);
                // temp.innerHTML = response.data.html_form_primary;

                document.querySelector("#dialog_add_datalink_primary").innerHTML = response.data.html_form_primary;
            }
        });
    }


    self.delete_datalink = function(hierarchy_path) {
        var data = {};
        data['mail_id'] = ark_mail_cpt_config.mail_id;
        data['hierarchy_path'] = hierarchy_path;
        data['action'] = "delete_datalink";

        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: data,
            success : function(response) {

                /** Rerender the datalink tree **/
                document.querySelector(".datalink_tree ul").innerHTML = response.data.html;
                self.datalinks = response.data.datalinks;
                /** Check if the active datalink was the deleted datalink, or child of said datalink. If it is, set the active datalink to the first root. **/
            }
        });

    }

    self.view_variable_sets = function(link_type, link_name, view_id, parent_link_type, element_row = null) {
        var data = {};
        data['mail_id'] = ark_mail_cpt_config.mail_id;
        data['link_name'] = link_name;
        data['link_type'] = link_type;

        data['action'] = "render_example_variable_set";

        data['formatting'] = view_id !== "dialog_add_datalink_variable_sets";
        data['parent_link_type'] = parent_link_type;

        if(element_row !== null) {
            var hierarchy_path = [];
            self.get_datalink_hierarchy_path(hierarchy_path, element_row);
            data['hierarchy_path'] = hierarchy_path;
        }

        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: data,
            success : function(response) {
                document.querySelector("#" + view_id).innerHTML = response.data.html;
            }
        });
    }

    self.toggle_variableset_visibility = function(parent) {
        //Get all the collapsible wrappers
        //collapse all wrappers that do not belong to the clcked toggle button

        //Toggle the target wrapper.
        parent.parentElement.querySelector("#" + parent.dataset.target).classList.toggle("collapsed");

        //Turn the arrow around
        parent.querySelector(".mail_composer_toggle-indicator").classList.toggle('expanded');
    }

    self.get_datalink_parent = function(datalink_row) {
        return datalink_row.closest("ul").previousElementSibling;
    }

    /**
     * Get the full path from a datalink_row root node all the way to the datalink_row as specified,
     */
    self.get_datalink_hierarchy_path = function(hierarchy_path, datalink_row) {

        if(datalink_row === null) {
            return;
        }

        self.get_datalink_hierarchy_path(hierarchy_path, self.get_datalink_parent(datalink_row));


        hierarchy_path.push(datalink_row.dataset.id);
    }

    /**
     *
     * @param hierarchy_path
     * @param link_type
     * @param link_name
     * @param many  - Is the relation _-to-many?
     * @param description
     */
    self.new_datalink = function(hierarchy_path, link_type, link_name, many, description) {
        var data = {};
        // data['link_type'] = e.target.dataset.link_type;
        // data['link_name'] = document.querySelector('#post_type_selector').value;
        data['mail_id'] = ark_mail_cpt_config.mail_id;
        data['action'] = 'add_datalink';
        data['hierarchy_path'] = hierarchy_path;
        data['link_type'] = link_type;
        data['link_name'] = link_name;
        data['many'] = many;

        data['desc'] = description;


        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: data,
            success : function(response) {
                document.querySelector(".datalink_tree ul").innerHTML = response.data.html;
                document.querySelector(".tb-close-icon").click();
                self.datalinks = response.data.datalinks;
            }
        });
    }


    /**
     * Returns all datalinks that have a 'to many' relationship with their parent
     *
     */
    self.get_toMany = function() {
        var to_many = [];

    }



    /** FORMATTING */


    self.add_format_function = function(e) {
        var given = e.target.value
        var function_name = given.substr(0, given.indexOf('('));
        var params = Object.keys(self.format_functions[function_name].args); //The parameters as required
        var format_arguments =  given.substr(given.indexOf('(')).replace("(", "").replace(")","").split(",");  //The given arguments in an array

        var arguments_dataset = {};

        for(var i = 0; i < params.length; i++) {
            var key = params[i];

            if(format_arguments[i] === undefined) {
                /** We are missing a parameter. Abort, and inform the user **/
                alert(self.msg.format_func_argument_missing + " " + key);
                return;
            }
            arguments_dataset[key] = format_arguments[i].trim();

        }

        /** VALIDATE **/
            //Get the string from the input between the  "(" and ")";
            //Explode the string, get all the variables.
            //Check if the variable count against the function data in the configuration

            //Create the HTML element
        var format_func = document.createElement('div')
        format_func.setAttribute('class', 'format_func');

        format_func.dataset.function_name = self.format_functions[function_name].name;
        format_func.dataset.arguments = JSON.stringify(arguments_dataset);

        //Removal button
        var remove_button = document.createElement("div");
        remove_button.setAttribute("class", "remove_func");
        remove_button.innerText = "X";

        //Func name element
        var func_name = document.createElement( "div");
        func_name.setAttribute("class", "func_name");
        func_name.innerText = given;


        //Wrap in container
        var format_func_container = document.createElement("div");
        format_func_container.setAttribute('class', 'format_func_container');

        format_func.appendChild(remove_button);
        format_func.appendChild(func_name);
        format_func_container.appendChild(format_func);

        e.target.closest(".format_option_adder").previousElementSibling.querySelector(".list").appendChild(format_func_container);

        //Run the variable formatting function
        self.format_variable(e.target.closest(".format_option_adder").previousElementSibling);


        /** Save the new function to the database **/
        self.save_format_list(e.target.closest(".format_option_adder").previousElementSibling);
    }

    self.save_format_list = function(datalink_variable) {

        var var_name = datalink_variable.querySelector(".var_name .value").innerText;
        var set_name = datalink_variable.closest(".collapsible-wrapper").previousElementSibling.dataset.set_name;
        var function_data = [];

        datalink_variable.querySelectorAll(".formatting .list .format_func").forEach(function(format_func) {
            function_data.push({
                "func_name" : format_func.dataset.function_name,
                "args" : format_func.dataset.arguments
            });
        })

        console.log(function_data);


        /** Get the hierarchy path to the currently active datalink **/
        var path = [];
        self.get_datalink_hierarchy_path(path, document.querySelector(".table_datalinks .active"));


        var data = {};
        // data['link_type'] = e.target.dataset.link_type;
        // data['link_name'] = document.querySelector('#post_type_selector').value;
        data['mail_id'] = ark_mail_cpt_config.mail_id;
        data['action'] = 'save_format_list';
        data['hierarchy_path'] = path;
        data['set_name'] = set_name;
        data['function_data'] = function_data
        data['var_name'] = var_name;


        jQuery.ajax({
            url: ark_mail_cpt_config.ajax_url,
            type: 'POST',
            method: 'POST',
            cache: false,
            data: data,
            success : function(response) {
                console.log(response);
            }
        });


        //Dataset name
        //variable_name => array

        //Dataset name => [
        //      variable_name => [
        //
        //          [
        //              "function_name" => "name1"
        //              "args" => values,
        //          ],
        //          [
        //              "function_name" => "name2"
        //              "args" => values,
        //          ]
        //      ]
        //]
    }

    /**
     * Upon typing in the format adder searcher, the user must get suggestions for format functions based on their input
     * @param e
     */
    self.suggest_format_functions = function(e) {
        jQuery(e.target).autocomplete({
            source: function(request, response) {
                var test = ["one", "Two", "substring"];
                var matcher = new RegExp( "^" + jQuery.ui.autocomplete.escapeRegex( request.term ), "i" );
                response(jQuery.grep(Object.keys(ark_mail_cpt_config.format_functions), function(item) {
                    return matcher.test(item);
                }) );
                // source: ark_mail_cpt_config.format_functions,


            },

            response: function(event, ui) {
                self.create_dropdown_elements(event.target.closest(".format_option_adder"), ui.content);
            },
            open : function(event, ui) {
                //Hide the standard Autocomplete dropdown
                jQuery(".ui-autocomplete").hide();
            },

            close: function(event, ui) {
            }



        })
    }

    /**
     * Create dropdown elements for the custom select, for the format functions
     * @param data
     */
    self.create_dropdown_elements = function(format_option_adder, data) {
        self.hide_dropdown_elements();

        var options_dropdown = format_option_adder.querySelector(".format_options");
        options_dropdown.classList.toggle("visible");
        options_dropdown.innerHTML = '';
        data.forEach(function(key) {
            var full_format_function_data = ark_mail_cpt_config.format_functions[key.value];
            var option = document.createElement('div');
            option.setAttribute( 'class', 'format_function_option');

            var title = document.createElement('div');
            title.setAttribute('class', 'function_title');
            /** The title will be of the format:  function_name(arg1, arg2, ..., argn) **/
            var title_text = key.value + "(" + Object.values(full_format_function_data.args).join(", ") + ")";
            title.innerText = title_text;

            var desc = document.createElement('div');
            desc.setAttribute('class', 'function_description');
            desc.innerText = full_format_function_data.desc;


            option.appendChild(title);
            option.appendChild(desc);
            options_dropdown.appendChild(option);
        });

    }


    self.hide_dropdown_elements = function() {
        document.querySelectorAll(".format_options.visible").forEach(function(dropdown) {
            dropdown.classList.toggle("visible");
        })
    }
    /**
     * Namecalling for the String Format options
     * @param function_name
     * @param subject
     * @param arguments
     */
    self.call_function_by_name = function(function_name, subject, func_arguments = []) {
        return window["String"]["prototype"][function_name].apply(subject, func_arguments );
    }

    /**
     * Function called after applying a new set of format options on a variable
     *
     * @param datalink_variable
     */
    self.format_variable = function(datalink_variable) {
        var variable_value = datalink_variable.querySelector(".var_value .original_value").innerText.trim();
        var format_options = datalink_variable.querySelectorAll(".formatting .list .format_func");


        format_options.forEach(function(format_option) {


            var function_map = self.map_php_to_js_functions(format_option.dataset.function_name, JSON.parse(format_option.dataset.arguments))

            // console.log(format_option);
            // console.log(format_option.innerText)
            // console.log("current value: " + variable_value)
            // console.log("calling function : " + function_map['name']);
            // console.log("with args: ");
            // console.log(function_map['args']);
            variable_value = self.call_function_by_name(function_map['name'], variable_value, function_map['args']);


        });

        datalink_variable.querySelector(".var_value .value").innerText = variable_value;

    }

    self.remove_format_function = function(format_function_container) {
        var datalink_variable = format_function_container.closest(".datalink_variable");
        format_function_container.remove();
        self.format_variable(datalink_variable);
        self.save_format_list(datalink_variable);
    }

    self.dragover_delegation = function(e) {
        if(e.target.classList.contains("format_func") || e.target.classList.contains("func_name") || e.target.classList.contains("format_func_container")) {

            //check at which side (vertical) the cursor is at.
            //If upper, insert a line before the element
            //If lower, insert a line after the element

            var target = e.target.closest(".format_func_container");
            var rect = target.getBoundingClientRect();
            var middle = rect.top + ((rect.bottom - rect.top)/2);

            document.querySelectorAll(".drag_before").forEach(function(element){ element.classList.remove("drag_before")});
            document.querySelectorAll(".drag_after").forEach(function(element){ element.classList.remove("drag_after")})


            if(e.clientY >= middle) {
                self.dragged_orientation = "before";
                target.classList.add("drag_before")

            }
            else {
                self.dragged_orientation = "after";
                target.classList.add("drag_after")

            }


            e.preventDefault();
        }
    }

    self.drop_delegation = function(e) {
        e.preventDefault();
        document.querySelectorAll(".drag_before").forEach(function(element){ element.classList.remove("drag_before")});
        document.querySelectorAll(".drag_after").forEach(function(element){ element.classList.remove("drag_after")})

        // console.log(self.dragged_index);
        // console.log(e.target);

        var target = e.target.closest(".format_func_container")
        var clone = target.cloneNode(true);
        var func_list = target.closest(".list");

        var functions = func_list.querySelectorAll(".format_func_container");
        var dragged_element = func_list.querySelectorAll(".format_func_container")[self.dragged_index];
        if(target === dragged_element) {
            return;
        }

        func_list.replaceChild(dragged_element, target);


        if(self.dragged_orientation === "before") {
            func_list.insertBefore(clone, dragged_element);
        }
        else {
            if(dragged_element.nextSibling === undefined) {
                func_list.appendChild(clone);
            }
            else {
                func_list.insertBefore(clone, dragged_element.nextSibling);
            }
        }


        /** Save the new configuration **/

        /** rerender **/

        self.format_variable(func_list.closest(".datalink_variable"));
    }

    /** A draggable element is being dragged
     * For now, we keep it simple: save the index of the dragged element, in the context of its parent
     * **/
    self.drag_delegation = function(e) {

        var list = e.target.closest(".list");
        self.dragged_index = [].indexOf.call(list.children, e.target.closest(".format_func_container"));;
    }


    self.map_php_to_js_functions = function(name, args){
        switch(name) {
            case 'substr':
                /** Php arguments are: offset and length.
                 *  JS arguments are:  startIndex and endIndex
                 * **/
                return {
                    'name' : 'substring',
                    'args' : [
                        parseInt(args['offset']),
                        parseInt(args['offset']) + parseInt(args['length'])
                    ]
                }
            case 'strtoupper':
                return {
                    'name' : 'toUpperCase',
                    'args' : []
                }
            case 'strtolower':
                return {
                    'name' : 'toLowerCase',
                    'args' : []
                }
            case 'str_repeat':
                return {
                    'name' : 'repeat',
                    'args' : [
                        parseInt(args['times'])
                    ]
                }
            default :
                return {
                    'name' : name,
                    'args' : []
                }
        }
    }



                                            /** END DATALINK TAB **/
    /** Variable suggestions **/

    self.var_suggestion_autocomplete_source = function(request, response) {
        var matcher = new RegExp( jQuery.ui.autocomplete.escapeRegex( request.term ), "i" );


        /** Format is :
         *
         * var_source = {
         *   'Boeking > Boeker > wp_comment': {
         *      data_ref: ['wc_booking', 'wp_users', 'wp_comment'],
         *      vars: {
         *          'comment_data' : ['content', 'user_id','date_created]
         *          }
         *   }
         * }
         *
         * So, loop through Label, then inner loop through vars
         */
        let filtered = [];
        Object.keys(self.var_source).forEach(function(label) {
            let vars = self.var_source[label].vars;

            let table_names = Object.keys(vars);
            for (let i = 0; i < table_names.length; i++) {
                let table_name = table_names[i];

                let current_filtered = jQuery.grep(vars[table_name], function(item) {
                    let result = matcher.test(item);
                    return result;
                });
                current_filtered.forEach(function(var_name){
                    filtered.push({label: label, value: var_name, table_name: table_name, data_ref : self.var_source[label].data_ref});
                });
            }
        });

        filtered.sort((a, b) => (a.label > b.label) ? 1 : ( (a.label < b.label) ? -1 : 0) );

        response(filtered);
    }
    self.render_variable_suggestions = function(items) {
        let current_cat = "";
        let ul = document.createElement("ul");
        let cat_ul = "";
        let cat_li = "";
        for(var i = 0; i < items.length; i++) {
            let item = items[i];

            if(item.label !== current_cat) {
                if(cat_ul !== "") {
                    ul.appendChild(cat_li);
                }
                current_cat = item.label;
                cat_li = document.createElement("li");
                cat_li.classList.add("cat_li");

                let cat_label = document.createElement("div");
                cat_label.classList.add("variable_suggestion_dataref");
                cat_label.innerText = item.label;

                cat_ul = document.createElement("ul");
                cat_li.appendChild(cat_label);
                cat_li.appendChild(cat_ul);

            }
            let li = document.createElement("li")
            li.classList.add("var_li");
            li.dataset.data_ref = JSON.stringify(item.data_ref);
            li.dataset.table_name = item.table_name;
            li.innerText = item.value;
            cat_ul.appendChild(li);
        };
        if(cat_li !== "") {
            ul.appendChild(cat_li);
        }

        return ul;
    }
    self.render_inpanel_applied_variable = function(variable_span) {
        console.log("rendering applied variable");
        let label = variable_span.dataset.category;
        let name = variable_span.innerHTML.substring(1, variable_span.innerHTML.length - 1);

        let applied_variable_li = document.createElement('li');
        let category_div = document.createElement('div');
        category_div.innerText = label;

        let name_li_ul = document.createElement('ul');
        let name_li = document.createElement('li');
        name_li.innerText = name;

        applied_variable_li.appendChild(category_div);
        applied_variable_li.appendChild(name_li_ul);
        name_li_ul.appendChild(name_li);


        return applied_variable_li;
    }

}

var amcpt = null;
document.addEventListener("DOMContentLoaded", function(e) {
    amcpt = new Ark_Mail_CPT_JS();
    amcpt.create();

    var ckeditor_instances = [];

    CKEDITOR.on( 'instanceReady', function(e) {
        var instance = function(e){

            var editor = e.editor;
            var focusManager = new CKEDITOR.focusManager(editor);
            var editor_selection;
            // var focusManager = new CKEDITOR.focusManager( editor );

            var panel = new CKEDITOR.ui.balloonPanel(editor, {
                title: 'Variable selection',
                content: '<div class="panel_head">Currently applied variable:</div><ul id="applied_variable"></ul> <div class="panel_head">Suggested variables:</div><ul id="variable_suggestions_panel"> </ul> <input type="hidden" id="var_suggestion_field" />'
            });

            editor.variable_panel = panel;

            var var_autocomplete = jQuery(panel.parts.content.$).find('#var_suggestion_field').autocomplete({
                minLength: 2,
                source: amcpt.var_suggestion_autocomplete_source,
                response: function(event, ui) {
                    jQuery(panel.parts.content.$).find('#variable_suggestions_panel').html(amcpt.render_variable_suggestions(ui.content));
                },
                open : function(event, ui) {
                    //Hide the standard Autocomplete dropdown
                    jQuery(".ui-autocomplete").hide();
                },
            });


            Object.keys(panel.focusables).forEach(function(key) {
                let focusable = panel.focusables[key];
                focusable.$.addEventListener("mousedown", balloon_panel_click);
                // focusable.$.addEventListener("mouseup", balloon_panel_click);
                // focusable.$.addEventListener("click", balloon_panel_click);
            });

            function balloon_panel_click(e) {
                e.preventDefault();
                if(e.target.classList.contains("var_li")) {
                    jQuery(panel.parts.content.$).find('#applied_variable').html(amcpt.render_inpanel_applied_variable(insert_clicked_variable(e.target)));
                }
                e.stopPropagation();
            }

            function insert_clicked_variable(clicked_element) {
                /** We want to replace whatever word we have selected with the variable **/

                /** Determine if we are altering an existing variable, or creating a new one **/
                let start_element = editor_selection.getStartElement();

                if(start_element.$.classList.contains("mc_ineditor_variable")) {
                    start_element.$.dataset.data_ref = clicked_element.dataset.data_ref;
                    start_element.$.dataset.table_name = clicked_element.dataset.table_name;
                    start_element.$.dataset.category = clicked_element.closest('.cat_li').querySelector('.variable_suggestion_dataref').innerText;
                    start_element.$.innerText = "{" + clicked_element.innerText + "}";

                    return start_element.$;
                }
                else {
                    let range = editor_selection.getRanges()[0];
                    let endOffset = range.endOffset;
                    let data = range.startContainer.$.data;
                    let result = /\S+$/.exec(data.slice(0, data.indexOf(' ',endOffset)));
                    let word = result ? result[0] : null;
                    if(word !== null && word.length > 0 ) {
                        // focusManager.focus();


                        /** First, we get the length of the word **/

                        let word_length = word.length - 1;

                        let caret_end_result = /\S+$/.exec(data.slice(0, endOffset));
                        let caret_end_word = caret_end_result ? caret_end_result[0] : null;
                        let caret_end_word_length = caret_end_word.length;


                        /** Calculate the beginning position of the selected word  **/
                        let new_start = range.endOffset - caret_end_word_length;

                        let new_range = editor.createRange();
                        let node = editor.editable().findOne('p');

                        new_range.setStart(range.startContainer, new_start);
                        new_range.setEnd(range.startContainer, new_start + word_length);

                        let new_selection = new CKEDITOR.dom.selection(start_element);
                        new_selection.selectRanges([new_range]);

                        let variable_element = document.createElement('span');
                        variable_element.classList.add("mc_ineditor_variable");
                        variable_element.dataset.data_ref = clicked_element.dataset.data_ref;
                        variable_element.dataset.table_name = clicked_element.dataset.table_name;
                        variable_element.dataset.category = clicked_element.closest('.cat_li').querySelector('.variable_suggestion_dataref').innerText;


                        variable_element.innerText = "{" + clicked_element.innerText + "}";
                        variable_element.setAttribute('style', "font-weight: bold; cursor:pointer; transition: 0.5s; background-color: #a8b9d15e;  padding: 3px; border-radius: 1px; box-shadow: 3px 3px 8px 0px;");


                        editor.insertHtml(variable_element.outerHTML + "&nbsp;", 'html', new_range);

                        return variable_element;
                    }
                }
            }

            function open_variable_balloon_panel(selection) {

                /** Check if we a variable has already been applied here.
                 *  Update the applied_variable element if necessary
                 * **/

                let variable_span;
                if(selection.type === CKEDITOR.NODE_ELEMENT) {
                    /** Get the variable span **/
                    if(selection.$.classList.contains('mc_ineditor_variable')) {
                        variable_span = selection.$;
                    }
                }
                else {
                    /** Get the variable span **/
                    let selection_parent = selection.getRanges()[0].startContainer.$.parentElement;
                    if(selection_parent.classList.contains('.mc_ineditor_variable')) {
                        variable_span = selection_parent;
                    }
                }

                if(variable_span !== undefined) {
                    jQuery(panel.parts.content.$).find('#applied_variable').html(amcpt.render_inpanel_applied_variable(variable_span));
                }

                Object.keys(CKEDITOR.instances).forEach(function(instance_key) {
                    CKEDITOR.instances[instance_key].variable_panel.hide();
                })
                panel.attach(selection);
                panel.blur();
            }

            /**
             * Upon typing in the format adder searcher, the user must get suggestions for format functions based on their input
             * @param e
             */
            function handle_keyup(e) {
                /** We need to check if we should open a dropdown for the variable list
                 *  For this, we need to check the wordt that is currently selected (or if the caret is inside the word)
                 * **/
                editor_selection = editor.getSelection();
                let start_element = editor_selection.getStartElement();
                let word = "";
                let range = editor_selection.getRanges()[0];

                let root = range.root
                let endOffset = range.endOffset;
                let data = range.startContainer.$.data;
                let node = "";

                /** First, we determine the currently selected word and its validity **/
                if(start_element.$.classList.contains("mc_ineditor_variable")) {

                    /** EDGE CASE: caret right outside of the }, signifying the end of the variable **/
                    let span_text = range.startContainer.$.wholeText;
                    if(span_text.charAt(span_text.length - 1) === '}' && e.data.$.keyCode !== 37) {   //Dont do this if we're going left (keycode 37)
                        if(range.startOffset === span_text.length) {
                            /** We should be outside the span right now **/

                            panel.hide();
                            let span = range.startContainer.$.parentNode;
                            if(span.nextSibling === null || span.nextSibling.data === "") {
                                span.parentElement.append('\u00A0');
                            }
                            let next_sib = new CKEDITOR.dom.element(span.nextSibling);
                            range.setStartBefore(next_sib);
                            range.collapse(true);
                            editor.getSelection().selectRanges([range]);
                            return;

                        }
                    }



                    /** The selection originates from inside of a span. This mean the word is the element's innerText. **/
                    word = start_element.$.innerText;
                    /** It also means the startOffsets of the range are off. We need to have it relative to the Root. **/
                    node = range.startContainer;

                }
                if(data !== undefined) {
                    let space_index = data.indexOf(' ',endOffset);
                    let data_slice = data.slice(0, space_index);
                    let result = /\S+$/.exec(data_slice);
                    word = result ? result[0] : null;
                    node = range.startContainer;
                }

                if(word !== null && word.length > 0 && word[0] === "{") {
                    /** The goal is to open a balloon panel in the middle of the currently selected string of text. **/
                    /** First, we get the length of the word **/

                    let word_length = word.length - 1;

                    let data_slice = data.slice(0, endOffset);
                    let caret_end_result =  /\S+$/.exec(data_slice);
                    let caret_end_word = caret_end_result ? caret_end_result[0] : null;
                    if(caret_end_word !== null) {
                        let caret_end_word_length = caret_end_word.length - 1;

                        /** Calculate the beginning position of the selected word  **/
                        let new_start = range.endOffset - caret_end_word_length;
                        let new_range = editor.createRange();

                        /** Here we set the selection to the middle of the word: new_start + (word_length / 2)**/
                        new_range.setStart(node, new_start + (word_length / 2));
                        new_range.setEnd(node, new_start + (word_length / 2) + 1);


                        /** Set the selected range, and attach the panel to this spot **/
                        let editable = editor.editable();
                        let new_selection = new CKEDITOR.dom.selection(range.startContainer);
                        new_selection.selectRanges([new_range]);
                        open_variable_balloon_panel(new_selection)
                        panel.blur();   /** Reset the focus to the text the user was editing **/

                        /** The balloon panel has been attached to the middle of the currently existing string.
                         *  With this, the caret has moved to the same selection range. Move it back.
                         * **/
                        editor.getSelection().selectRanges([range]);




                        let word_to_search = word.substr(1);
                        if(word_to_search[word_to_search.length - 1] === "}") {
                            word_to_search = word_to_search.slice(0,-1);
                        }

                        jQuery(panel.parts.content.$).find('#var_suggestion_field').autocomplete("search", word_to_search);
                    }
                    else {
                        panel.hide();

                    }
                }
                else {
                    panel.hide();
                }
            }

            editor.editable().on('keyup', function( e ) {
                switch(e.data.$.keyCode) {
                    case e.data.$.keyCode === CKEDITOR.SHIFT + 219:
                    default:
                        handle_keyup(e);
                        break;
                    case CKEDITOR.SHIFT:
                        break;
                }
            } );

            editor.editable().on('mouseup', function(e){

                let target = e.data.getTarget();
                if(target.$.classList.contains('mc_ineditor_variable')) {
                    let word_to_search = target.$.innerText.substring(1, target.$.innerText.length - 1);
                    jQuery(panel.parts.content.$).find('#var_suggestion_field').autocomplete("search", word_to_search);
                    open_variable_balloon_panel(target);
                }
                else {
                    let range = editor.getSelection().getRanges()[0];
                    if(range !== undefined) {
                        if(range.startContainer.$.parentNode.classList.contains('mc_ineditor_variable') && range.startOffset === range.startContainer.$.length) {
                            /** EDGE CASE: clicking after a variable span **/
                            /** CKeditor normally wants to open it now, because you cant select a (non existant) <p> element right after a span
                             * So here, we just manually create a <p> element and select it
                             * **/

                            let span = range.startContainer.$.parentNode;
                            if(span.nextSibling === null) {
                                span.parentElement.append(" ");
                            }
                            let next_sib = new CKEDITOR.dom.element(span.nextSibling);
                            range.setStartBefore(next_sib);
                            range.collapse(true);
                            editor.getSelection().selectRanges([range]);
                        }
                        handle_keyup(e);
                    }
                }

            });
        }
        instance(e);
    } );

    /** make anchor tags editable with ckeditor **/

    CKEDITOR.dtd.$editable['a'] = 1;
});

