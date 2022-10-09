define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                exportTypes:['excel'],
                exportOptions: {
                    fileName: '用户信息_' + Moment().format("YYYY-MM-DD"),
                    ignoreColumn: [0, 'operate', 'avatar'] //默认不导出第一列(checkbox)与操作(operate)列
                },
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), sortable: true, operate: false},
                        // {field: 'username', title: __('Username')},
                        {field: 'nickname', title: __('昵称')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false},
                        {field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'loginip', title: __('Loginip'), formatter: Table.api.formatter.search},
                        {field: 'jointime', title: __('Jointime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},
                        {field: 'joinip', title: __('Joinip'), formatter: Table.api.formatter.search},
                        {field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: {normal: __('Normal'), hidden: __('Hidden')}},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            // buttons: [{
                            //         name: 'detail',
                            //         text: __('Detail'),
                            //         icon: 'fa fa-list',
                            //         classname: 'btn btn-info btn-xs btn-detail btn-dialog',
                            //         url: 'user/user/detail'
                            //     }],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
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