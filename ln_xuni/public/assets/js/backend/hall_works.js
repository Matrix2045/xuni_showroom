define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'hall_works/index' + location.search,
                    add_url: 'hall_works/add?hall_id='+ids,
                    edit_url: 'hall_works/edit?hall_id='+ids,
                    table: 'hall_works',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url+'&ids='+ids,
                pk: 'id',
                sortName: 'weigh',
                sortOrder: 'ase',
                showExport: false,
                pagination:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'no', title: __('所在位置'), operate:false,
                            formatter: function (value, row, index) {
                                //获取每页显示的数量
                                var pageSize=table.bootstrapTable('getOptions').pageSize;
                                //获取当前是第几页
                                var pageNumber=table.bootstrapTable('getOptions').pageNumber;
                                //返回序号，注意index是从0开始的，所以要加上1
                                return pageSize * (pageNumber - 1) + index + 1;
                            }
                        },
                        {field: 'hall.name', title: __('Hall_id'), operate:false},
                        {field: 'name', title: __('Name')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'author', title: __('Author')},
                        // {field: 'type', title: __('Type'), searchList: {"one":__('Type one'),"two":__('Type two')}, formatter: Table.api.formatter.normal},
                        {field: 'width', title: "实际宽度/单位cm"},
                        {field: 'height', title: "实际高度/单位cm"},
                        {field: 'cratetime', title: __('Cratetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            $('.btn-showimage').click(function () {
                var data = [{src:Fast.api.cdnurl('/uploads/pmt.jpg')}];
                Layer.photos({
                    photos: {
                        "start": $(this).parent().index(),
                        "data": data
                    },
                    anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
                });
            });
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