<script>
    function f(app) {

        const vModelComponent = <?=$vModelComponent?>;
        const listComponent = <?=$listComponent?>;

        app.component('fields', {
            props: {
                modelValue: {
                    type: Array,
                    default: () => []
                },
                modelInfo: {
                    type: Object
                },
                modelName: {
                    type: String
                }
            },
            emits: ["update:modelValue"],
            setup(props, {emit}) {
                const {Render} = Surface
                const {toRef, ref} = Vue

                const initField = item => {
                    item.sort = item.sort || 0
                    item.table_type = item.table_type || listComponent[0].value
                    item.search_type = item.search_type || ""
                    item.form_type = item.form_type || ""
                    item.comment = item.comment || item.name || ""
                    return item
                }

                const data = props.modelValue ? toRef(props, 'modelValue') : ref([])
                const change = () => {
                    emit('update:modelValue', data.value)
                }

                for (const k in data.value) {
                    initField(data.value[k])
                }

                change()

                let customNotice = "支持输入自定义渲染（继承curd/\Generator）"

                return () => {
                    return new Render({
                        el: 'el-table',
                        props: {
                            data: data.value
                        },
                        children: [
                            {
                                el: 'el-table-column',
                                props: {
                                    prop: 'sort',
                                    width: 110,
                                    label: '排序',
                                    sortable: true,
                                    'sort-method': (a, b) => a.sort > b.sort ? 1 : -1
                                },
                                children: [
                                    scope => {
                                        return {
                                            el: 'number',
                                            props: {
                                                style: {
                                                    width: '80px'
                                                },
                                                'controls-position': "right",
                                                modelValue: scope.row.sort || 0,
                                                'onUpdate:modelValue'(val) {
                                                    scope.row.sort = val
                                                    change()
                                                }
                                            }
                                        }
                                    }
                                ]
                            },
                            {
                                el: 'el-table-column',
                                props: {
                                    prop: 'name',
                                    label: '字段'
                                },
                                children: scope => {
                                    return {
                                        el: 'input',
                                        props: {
                                            modelValue: scope.row.name,
                                            'onUpdate:modelValue'(val) {
                                                scope.row.name = val
                                                change()
                                            }
                                        }
                                    }
                                }
                            },
                            {
                                el: 'el-table-column',
                                props: {
                                    prop: 'comment',
                                    width: 200,
                                    label: '名称'
                                },
                                children: scope => {
                                    return {
                                        el: 'input',
                                        props: {
                                            modelValue: scope.row.comment,
                                            'onUpdate:modelValue'(val) {
                                                scope.row.comment = val
                                                change()
                                            }
                                        }
                                    }
                                }
                            },
                            // {
                            //     el: 'el-table-column',
                            //     props: {
                            //         prop: 'type',
                            //         label: '类型'
                            //     }
                            // },
                            {
                                el: 'el-table-column',
                                props: {
                                    prop: 'table_type',
                                    width: 200,
                                    label: '列表'
                                },
                                children: [
                                    scope => {
                                        return {
                                            el: 'select',
                                            props: {
                                                clearable: true,
                                                filterable: true,
                                                'allow-create': true,
                                                options: listComponent,
                                                modelValue: scope.row.table_type || '',
                                                'onUpdate:modelValue'(val) {
                                                    scope.row.table_type = val
                                                    change()
                                                }
                                            }
                                        }
                                    },
                                    {
                                        el: 'div',
                                        slot: "header",
                                        props: {
                                            style: {
                                                display: 'flex',
                                                "align-items": 'center'
                                            }
                                        },
                                        children: [
                                            "列表",
                                            {
                                                el: 'el-tooltip',
                                                props: {
                                                    content: customNotice
                                                },
                                                children: {el: 'icon', props: {icon: 'Warning'}}
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                el: 'el-table-column',
                                props: {
                                    prop: 'search_type',
                                    width: 200,
                                    label: '搜索'
                                },
                                children: [scope => {
                                    return {
                                        el: 'select',
                                        props: {
                                            clearable: true,
                                            filterable: true,
                                            'allow-create': true,
                                            options: vModelComponent,
                                            modelValue: scope.row.search_type || '',
                                            'onUpdate:modelValue'(val) {
                                                scope.row.search_type = val
                                                change()
                                            }
                                        }
                                    }
                                },
                                {
                                    el: 'div',
                                    slot: "header",
                                    props: {
                                        style: {
                                            display: 'flex',
                                            "align-items": 'center'
                                        }
                                    },
                                    children: [
                                        "搜索",
                                        {
                                            el: 'el-tooltip',
                                            props: {
                                                content: customNotice
                                            },
                                            children: {el: 'icon', props: {icon: 'Warning'}}
                                        }
                                    ]
                                }
                                ]
                            },
                            {
                                el: 'el-table-column',
                                props: {
                                    prop: 'form_type',
                                    width: 200,
                                    label: '编辑'
                                },
                                children: [
                                    scope => {
                                        return {
                                            el: 'select',
                                            props: {
                                                clearable: true,
                                                filterable: true,
                                                'allow-create': true,
                                                options: vModelComponent,
                                                modelValue: scope.row.form_type || '',
                                                'onUpdate:modelValue'(val) {
                                                    scope.row.form_type = val
                                                    change()
                                                }
                                            }
                                        }
                                    },
                                    {
                                        el: 'div',
                                        slot: "header",
                                        props: {
                                            style: {
                                                display: 'flex',
                                                "align-items": 'center'
                                            }
                                        },
                                        children: [
                                            "编辑",
                                            {
                                                el: 'el-tooltip',
                                                props: {
                                                    content: customNotice
                                                },
                                                children: {el: 'icon', props: {icon: 'Warning'}}
                                            }
                                        ]
                                    }
                                ]
                            },
                            {
                                el: 'el-table-column',
                                props: {
                                    width: 60,
                                },
                                children: scope => {
                                    return {
                                        el: 'button',
                                        props: {
                                            icon: ElementPlusIconsVue.Close,
                                            type: 'text',
                                            onClick() {
                                                data.value.splice(scope["\$index"], 1)
                                                change()
                                            }
                                        }
                                    }
                                }
                            },
                            {
                                el: "div",
                                slot: "append",
                                props: {
                                    style: {
                                        padding: '10px',
                                    }
                                },
                                children: [
                                    {
                                        el: 'el-button',
                                        props: {
                                            onClick:()=> {
                                                data.value.push(initField({}))
                                                change()
                                            }
                                        },
                                        children: "+ 增加一行"
                                    }
                                ]
                            }
                        ]
                    }).render()
                }
            }
        })
    }
</script>
