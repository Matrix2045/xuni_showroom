define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'hall/index' + location.search,
                    add_url: 'hall/add',
                    edit_url: 'hall/edit',
                    del_url: 'hall/del',
                    multi_url: 'hall/multi',
                    table: 'hall',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                showExport: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'admin.username', title: __('管理员')},
                        {field: 'hall_type.name', title: "对应场馆", operate:false},
                        {field: 'name', title: __('Name')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'curator', title: __('Curator')},
                        {field: 'organizer', title: __('Organizer')},
                        {field: 'is_show', title: __('Is_show'), searchList: {"yes":__('Is_show yes'),"no":__('Is_show no')}, formatter: Table.api.formatter.normal},
                        {field: 'is_recommend', title: __('Is_recommend'), searchList: {"yes":__('Is_recommend yes'),"no":__('Is_recommend no')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [{
                                name: 'detail',
                                text: __('作品列表'),
                                icon: 'fa fa-list',
                                classname: 'btn btn-info btn-xs btn-detail btn-dialog btn-dialogsddsd',
                                url: 'Hall_works/index'
                            }],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                $(".btn-dialogsddsd").data("area", ["100%", "100%"]);

            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});