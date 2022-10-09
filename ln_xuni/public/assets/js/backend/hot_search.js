define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'hot_search/index' + location.search,
                    add_url: 'hot_search/add',
                    del_url: 'hot_search/del',
                    table: 'hot_search',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                search: false,
                showExport: false,
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('id'), operate:false},
                        {field: 'keyword', title: __('关键词'), operate:'like'},
                        {field: 'add_time', title: __('添加时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'sort', title: __('排序')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
            var tpl =   "<tr><td width='100px'><input class='form-control' name='row[keyword][]' /></td>" + 
                        "<td width='100px'><input class='form-control' value='50' name='row[sort][]' /></td>" +
                        "<td width='60px'><span class='btn btn-danger btn-del btn-sm'>删除</span></td>" + 
                        "</tr>";
            $('.btn-add-keywords', document).on('click', function() {
                $('.keyword-table tbody').append(tpl)
                $('.btn-del').unbind('click')
                $('.btn-del').click(function() {
                    $($(this).parents('tr')[0]).remove()
                })
            })
            Hook.listen('form')
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };
    return Controller;
});