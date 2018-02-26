---
layout:         post
title:          handsontable 用法笔记
date:           2017-12-21 20:56:07--0800
summary:        开发中用到了handsontable，官网的demos、usage和api查阅起来不是很方便，在学习过程中自己整理了一下。版本基于社区版v0.35.0，专业版v1.15.0。
categories:     notes
update_date:    2018-1-9 03:35:54
---

{:TOC}

##  简介

handsontable-JavaScript Spreadsheet Component For Web Apps，这是官网的title。它是一个component。作为JavaScript组件，handsontable的特点是大数据高性能，样式与Excel非常相似。可以在web中呈现和Excel里一样的表格数据。并可在前端完成一系列复杂的数据分析及操作功能。

版本发布：

- Handsontable Community Edition（Open Source）
- Handsontable Pro（commercial license）

两个版本均托管在[github](https://github.com/handsontable/)。pro版在使用时会在html中以及console中添加显示如下内容：（v1.15.0）

> Evaluation version of Handsontable Pro. Not licensed for use in a production environment

如使用pro版建议购买license尊重作者的劳动成果。

{% if  site.url=="https://{{ site.github_username }}.github.io" %}
<link rel="STYLESHEET" type="text/css" href="https://docs.handsontable.com/pro/1.15.0/bower_components/handsontable-pro/dist/handsontable.min.css">
<script src="https://docs.handsontable.com/pro/1.15.0/bower_components/handsontable-pro/dist/handsontable.min.js"></script>
{% else %}
<link rel="STYLESHEET" type="text/css" href="/static/handsontable-pro/handsontable.full.min.css">
<script src="/static/handsontable-pro/handsontable.full.min.js"></script>
{% endif %}

## 基本用法

### 干货写前面

#### 方法调用

先构造一个handsontable，然后直接使用，或者使用jQuery的封装形式。

```javascript
// all following examples assume that you constructed Handsontable like this
var ht = new Handsontable(document.getElementById('example1'), options);

// now, to use setDataAtCell method, you can either:
ht.setDataAtCell(0, 0, 'new value');

$('#example1').handsontable('setDataAtCell', 0, 0, 'new value');
```

#### 构造函数的options参数

``` javascript
data:               # 数据源（二维数组、对象）
startRows:          # 初始行数
startCols:          # 初始列数
rowHeaders: true    # 行标 []
colHeaders: true    # 列名 可以设置为自定义数组 []
colWidths:          # 列宽度 integer:均等 or []
autoColumnSize:true # 列宽自适应
filters:            # 过滤
dropdownMenu:true   # 下拉菜单
minSpareRows:       # 最小多余的行数
minSpareCols:       # 最小多余的列数
afterChange: function () {}, # 修改显示后的回调函数
manualColumnMove：  # 列移动 
manualRowMove: true # 行移动 
manualColMove: true # 列移动 true
// https://docs.handsontable.com/pro/1.15.0/demo-moving.html
stretchH:           # 拉伸高度 默认:"none" 可选 "last" "all" 用于父级不可滚动时
// https://docs.handsontable.com/pro/1.15.0/demo-stretching.html
copyPaste:          # 复制粘贴选项
search:             # 启动搜索插件
```

### 常用API

```
hot.loadData()
hot.getData()             // 数组格式
got.getSourceData()       // JSON格式（columns里配置data）
hot.search.query()        // 全局搜索并高亮，返回 [{row,col,data},...]
```

调用方法

```javascript
// all following examples assume that you constructed Handsontable like this
var ht = new Handsontable(document.getElementById('example1'), options);

// now, to use setDataAtCell method, you can either:
ht.setDataAtCell(0, 0, 'new value');

# Alternatively, you can call the method using jQuery wrapper (obsolete, requires initialization using our jQuery guide

$('#example1').handsontable('setDataAtCell', 0, 0, 'new value');
```

#### hook #event:afterChange

##### Parameters:

| Name      | Type   | Description                              |
| --------- | ------ | ---------------------------------------- |
| `changes` | Array  | 2D array containing information about each of the edited cells `[[row, prop, oldVal, newVal], ...]`. |
| `source`  | String | optionalString that identifies source of hook call([list of all available sources](http://docs.handsontable.com/tutorial-using-callbacks.html#page-source-definition)). |

#### hook #event:afterValidate

如果定义了validator函数，执行之后会触发该事件。并执行响应函数，函数的第一个参数是验证结果，用来确定是否验证通过。

##### Parameters:

| Name      | Type            | Description                                                  |
| --------- | --------------- | ------------------------------------------------------------ |
| `isValid` | Boolean         | `true` if valid, `false` if not.                             |
| `value`   | *               | The value in question.                                       |
| `row`     | Number          | Row index.                                                   |
| `prop`    | String \|Number | Property name / column index.                                |
| `source`  | String          | optionalString that identifies source of hook call([list of all available sources](http://docs.handsontable.com/tutorial-using-callbacks.html#page-source-definition)). |

#### updateSettings

在初始化之后，更新handsontable的设置

```javascript
var hot = new Handsontable(example, settings);
hot.updateSettings(Settings);
// Settings 是 json 结构 {} 如：
hot.updateSettings({
  contextMenu: {
    callback: function (key, options){
      xxxxxx
    },
    items:{
      xxx:{},
      yyy:{}
    }
  }  
});
```

### 快速开始

1. 在网页中引入`js`和`css`文件。推荐引入`handsontable.full.min.js`和`handsontable.full.min.css`。
2. 在html中添加容器`<div id="example"></div>`。
3. 用`JavaScript`初始化

```javascript
var data = [
  ["", "Ford", "Tesla", "Toyota", "Honda"],
  ["2017", 10, 11, 12, 13],
  ["2018", 20, 11, 14, 13],
  ["2019", 30, 15, 12, 13]
];

var container = document.getElementById('example');
var hot = new Handsontable(container, {
  data: data,
  rowHeaders: true,
  colHeaders: true,
  filters: true,
  dropdownMenu: true
});
```

浏览器打开即可查看效果：

<div id="example"></div>
<style>
table.htCore{font-size:12px;font-family: Verdana, Helvetica, Arial, FreeSans, sans-serif;}
table.htCore input[type="text"]{height:15px;font-size:12px;margin-bottom:0;}
table.htCore a{background-image: none;}
table.htCore label{font-size:15px;}
// 调整css防blog样式污染。
</style>
<script>
var data = [
  ["", "Ford", "Tesla", "Toyota", "Honda"],
  ["2017", 10, 11, 12, 13],
  ["2018", 20, 11, 14, 13],
  ["2019", 30, 15, 12, 13]
];

var container = document.getElementById('example');
var hot = new Handsontable(container, {
  data: data,
  rowHeaders: true,
  colHeaders: true,
  filters: true,
  dropdownMenu: true
});
</script>

### 数据绑定

#### 理解作为引用的绑定

Handsontable通过引用与你的数据源(数组或对象)绑定。因此，特点有二：

- 在网格中输入的所有数据都将改变原始数据源。
- 从Handsontable外部更改数据源。除非在屏幕上使用呈现方法`render()`刷新网格，否则将不会在屏幕上显示更改。

```javascript
 var
    data1 = [
      ['', 'Tesla', 'Nissan', 'Toyota', 'Honda', 'Mazda', 'Ford'],
      ['2017', 10, 11, 12, 13, 15, 16],
      ['2018', 10, 11, 12, 13, 15, 16],
      ['2019', 10, 11, 12, 13, 15, 16],
      ['2020', 10, 11, 12, 13, 15, 16],
      ['2021', 10, 11, 12, 13, 15, 16]
    ],
    container1 = document.getElementById('example1'),
    settings1 = {
      data: data1
    },
    hot1;

  hot1 = new Handsontable(container1, settings1);
  data1[0][1] = 'Ford'; // change "Kia" to "Ford" programmatically
  hot1.render();

```

#### 更改数据源而不更改显示

可以在引用数据时，引用数据源的一个单独的副本。这样数据源变化就不会影响数据显示了。

```javascript
...

hot2 = new Handsontable(container2, {
    data: JSON.parse(JSON.stringify(data2))
  });
```

#### 更改显示而不更改数据源

保存之前先克隆，`tmpData`即是显示修改之前的数据副本。

```javascript
...

hot3 = new Handsontable(container3, {
    data: data3,
    afterChange: function () {
      var tmpData = JSON.parse(JSON.stringify(data3));
      // now tmpData has a copy of data3 that can be manipulated
      // without breaking the Handsontable data source object
    }
  });
```

### 数据来源

Handsontable是如何使用各种数据源的？

#### 数组类型数据源

```javascript
   var
    data = [
      ['', 'Tesla', 'Nissan', 'Toyota', 'Honda', 'Mazda', 'Ford'],
      ['2017', 10, 11, 12, 13, 15, 16],
      ['2018', 10, 11, 12, 13, 15, 16],
      ['2019', 10, 11, 12, 13, 15, 16],
      ['2020', 10, 11, 12, 13, 15, 16],
      ['2021', 10, 11, 12, 13, 15, 16]
    ],
    container1 = document.getElementById('example1'),
    hot1;

  hot1 = new Handsontable(container1, {
    data: data,
    startRows: 5,
    startCols: 5,
    colHeaders: true,
    minSpareRows: 1
  });
```

#### 含隐藏列的数组类型数据源

```javascript
  var
    hiddenData = [
      ['', 'Tesla', 'Nissan', 'Toyota', 'Honda', 'Mazda', 'Ford'],
      ['2017', 10, 11, 12, 13, 15, 16],
      ['2018', 10, 11, 12, 13, 15, 16],
      ['2019', 10, 11, 12, 13, 15, 16],
      ['2020', 10, 11, 12, 13, 15, 16],
      ['2021', 10, 11, 12, 13, 15, 16]
    ],
    container = document.getElementById('example2'),
    hot2;

  hot2 = new Handsontable(container, {
    data: hiddenData,
    colHeaders: true,
    minSpareRows: 1,
    columns: [
      {data: 0},
      {data: 2},
      {data: 3},
      {data: 4},
      {data: 5},
      {data: 6}
    ]
  });
```

#### 对象数据源

```javascript
  var
    objectData = [
      {id: 1, name: 'Ted Right', address: ''},
      {id: 2, name: 'Frank Honest', address: ''},
      {id: 3, name: 'Joan Well', address: ''},
      {id: 4, name: 'Gail Polite', address: ''},
      {id: 5, name: 'Michael Fair', address: ''},
    ],
    container3 = document.getElementById('example3'),
    hot3;

  hot3 = new Handsontable(container3, {
    data: objectData,
    colHeaders: true,
    minSpareRows: 1
  });

```

#### 用列做函数的对象数据源

```javascript
  var nestedObjects = [
        {id: 1, name: {first: "Ted", last: "Right"}, address: ""},
        {id: 2, address: ""}, // HOT will create missing properties on demand
        {id: 3, name: {first: "Joan", last: "Well"}, address: ""}
      ],
      container4 = document.getElementById('example4'),
      hot4;

  hot4 = new Handsontable(container4, {
    data: nestedObjects,
    colHeaders: true,
    columns: function(column) {
      var columnMeta = {};
      if (column === 0) {
        columnMeta.data = 'id';
      } else if (column === 1) {
        columnMeta.data = 'name.first';
      } else if (column === 2) {
        columnMeta.data = 'name.last';
      } else if (column === 3) {
        columnMeta.data = 'address';
      } else {
        columnMeta = null;
      }
      return columnMeta;
    },
    minSpareRows: 1
  });
```

其实上面的闭包也可以写成这样：

```javascript
  columns: [
    {data: 'id'},
    {data: 'name.first'},
    {data: 'name.last'},
    {data: 'address'}
  ],
```

#### 自定义数据结构及列名的对象数据源

```javascript
dataSchema: {id: null, name: {first: null, last: null}, address: null},
colHeaders: ['ID', 'First Name', 'Last Name', 'Address'],
```
> 官网有个高级用法，感兴趣可自己阅读理解一下：
>
> <https://docs.handsontable.com/pro/1.15.0/tutorial-data-sources.html#page-property-schema>



### 数据的加载与保存

#### 使用onChange回调保存数据

定义在初始化Handsontable时`option`选项参数：`afterChange`

```javascript
 afterChange: function (change, source) {
      if (source === 'loadData') {
        return; //don't save this change
      }
      if (!autosave.checked) {
        return;
      }
      clearTimeout(autosaveNotification);
      ajax('scripts/json/save.json', 'GET', JSON.stringify({data: change}), function (data) {
        exampleConsole.innerText  = 'Autosaved (' + change.length + ' ' + 'cell' + (change.length > 1 ? 's' : '') + ')';
        autosaveNotification = setTimeout(function() {
          exampleConsole.innerText ='Changes will be autosaved';
        }, 1000);
      });
    }
  });

  Handsontable.dom.addEvent(load, 'click', function() {
    ajax('scripts/json/load.json', 'GET', '', function(res) {
      var data = JSON.parse(res.response);

      hot.loadData(data.data);
      exampleConsole.innerText = 'Data loaded';
    });
  });

  Handsontable.dom.addEvent(save, 'click', function() {
    // save all cell's data
    ajax('scripts/json/save.json', 'GET', JSON.stringify({data: hot.getData()}), function (res) {
      var response = JSON.parse(res.response);

      if (response.result === 'ok') {
        exampleConsole.innerText = 'Data saved';
      }
      else {
        exampleConsole.innerText = 'Save error';
      }
    });
  });

  Handsontable.dom.addEvent(autosave, 'click', function() {
    if (autosave.checked) {
      exampleConsole.innerText = 'Changes will be autosaved';
    }
    else {
      exampleConsole.innerText ='Changes will not be autosaved';
    }
  });
```

#### 本地保存

启用数据存储机制，可在初始化时设置`persistentState`参数为`true`，或者使用`updateSettings `方法。

```javascript
persistentStateSave (key: String, value: Mixed)
# 在浏览器本地存储保存给定键的值
persistentStateLoad (key: String, valuePlaceholder: Object)
# 根据已知键读取值，值被保存valuePlaceholder.value
persistentStateReset (key: String)
# 清除保存的值，无指定key则清除所有
```

使用这三个钩子的api，是为了防止同页面有2个以上实例，数据只能保存一份的尴尬。

### 设置选项

单元格选项，在构造时定义配置

```javascript
var hot = new Handsontable(document.getElementById('example'), {
  cell: [
    {row: 0, col: 0, readOnly: true}
  ]
});
```

或者定义函数属性

```javascript
var hot = new Handsontable(document.getElementById('example'), {
  cells: function (row, col, prop) {
    var cellProperties = {}

    if (row === 0 && col === 0) {
      cellProperties.readOnly = true;
    }

    return cellProperties;
  }
})
```

级联的配置模型，在构造函数使用第一级提供的配置选项或者`updateSettings`方法，使用二级对象提供的配置选项，

### 回调函数

<https://docs.handsontable.com/pro/1.15.0/tutorial-using-callbacks.html>

### 自定义样式

<https://docs.handsontable.com/pro/1.15.0/tutorial-styling.html>



## Setting options

### Introduction to cell options

Any constructor or column option may be overwritten for a particular cell (row/column combination),      using `cell` array passed to the Handsontable constructor. Example:

```javascript
var hot = new Handsontable(document.getElementById('example'), {
  cell: [
    {row: 0, col: 0, readOnly: true}
  ]
});
```

Or using cells function property to the Handsontable constructor. Example:

```javascript
var hot = new Handsontable(document.getElementById('example'), {
  cells: function (row, col, prop) {
    var cellProperties = {}

    if (row === 0 && col === 0) {
      cellProperties.readOnly = true;
    }

    return cellProperties;
  }
})
```

### How does the Cascading Configuration work?

Since Handsontable 0.9 we use Cascading Configuration, which is a fast way to provide configuration options      for the whole table, along with its columns and particular cells.

Consider the following example:

```javascript
var hot = new Handsontable(document.getElementById('example'), {
  readOnly: true,
  columns: [
    {readOnly: false},
    {},
    {}
  ],
  cells: function (row, col, prop) {
    var cellProperties = {}

    if (row === 0 && col === 0) {
      cellProperties.readOnly = true;
    }

    return cellProperties;
  }
});
```

The above notation will result in all TDs being read only, except for first column TDs      which will be editable, except for the TD in top left corner which will still be read only.

### The cascading configuration model

The Cascading Configuration model is based on prototypal inheritance. It is much faster and memory      efficient compared to the previous model that used jQuery extend. See it yourself:      <http://jsperf.com/extending-settings>

- **Constructor**

  Configuration options that are provided using first-level

  ```javascript
  new Handsontable(document.getElementById('example'), {
    option: 'value'
  });
  ```

  and `updateSettings` method.

- **Columns**

  Configuration options that are provided using second-level object

  ```javascript
  new Handsontable(document.getElementById('example'), {
    columns: {
      option: 'value'
    }
  });
  ```

- **Cells**

  Configuration options that are provided using second-level function

  ```javascript
  new Handsontable(document.getElementById('example'), {
    cells: function(row, col, prop) {

    }
  });
  ```

## [Using callbacks](https://docs.handsontable.com/pro/1.15.0/tutorial-using-callbacks.html)

Please visit：<https://docs.handsontable.com/pro/1.15.0/tutorial-using-callbacks.html>



## Styling

Commonly used styles:



​      There is very little you can't do with Handsontable. As it doesn't impose any specific theme,      you can play with CSS however you like. Keep in mind that Handsontable needs to calculate the      width and height of elements inside it to control the scrollbar, so the complex styling rules      may affect the performance.    

​      Some of the recipes listed below need an additional parent class/id or other modifications      to override the default values. Also, the styles might slightly vary depending on your configuration.      The below examples were tested with a 10x10 grid with both row and column headers turned on.    

### Table

​        **Background**      

```css
.ht_master tr td {
  background-color: #F00;
}
```

### Headers

​        **Background**      

```css
/* All headers */
.handsontable th {
  background-color: #F00;
}

/* Row headers */
.ht_clone_left th {
  background-color: #F00;
}

/* Column headers */
.ht_clone_top th {
  background-color: #F00;
}
```

​        **Borders**      

```css
/* Row headers */
/* Bottom */
.ht_clone_top_left_corner th {
  border-bottom: 1px solid #F00;
}

/* Left and right */
.ht_clone_left th {
  border-right: 1px solid #F00;
  border-left: 1px solid #F00;
}

/* Column headers */
/* Top, bottom and right */
.ht_clone_top th {
  border-top: 1px solid #F00;
  border-right: 1px solid #F00;
  border-bottom: 1px solid #F00;
}

/* Left */
.ht_clone_top_left_corner th {
  border-right: 1px solid #F00;
}
```

### Corner

​        **Background**      

```css
.ht_clone_top_left_corner th {
  background-color: #F00;
}
```

​        **Borders**      

```css
.ht_clone_top_left_corner th {
  border: 1px solid #F00;
}
```

### Rows

​        **Background**      

```css
/* Every odd row */
.ht_master tr:nth-of-type(odd) > td {
  background-color: #f00;
}

/* Every even row */
.ht_master tr:nth-of-type(even) > td {
  background-color: #f00;
}

/* Selected row  */
/* Add a custom class name in the configuration: currentRowClassName: "foo"; */
.ht_master tr.foo > td {
  background-color: #f00;
}

/* Specific row (2) */
.ht_master tr:nth-child(2) > td {
  background-color: #f00;
}
```

​        **Borders**      

```css
/* Bottom */
.ht_master tr > td {
  border-bottom: 1px solid #F00;
}

/* Right */
.ht_master tr > td {
  border-right: 1px solid #F00;
}
```

### Columns

​        **Background**      

```css
/* Every odd column */
.ht_master tr > td:nth-of-type(odd) {
  background-color: #f00;
}

/* Every even column */
.ht_master tr > td:nth-of-type(even) {
  background-color: #f00;
}

/* Selected column  */
/* Add a custom class name in the configuration: currentColClassName: "foo"; */
.ht_master tr > td.foo {
  background-color: #f00;
}

/* Specific column (B) */
.ht_master tr > td:nth-child(3) {
  background-color: #f00;
}
```

### Cell

​        **Background**      

```css
/* Selected cell */
.ht_master tr > td.current {
  background-color: #F00;
}

/* Specific cell (B2) */
.ht_master tr:nth-child(2) > td:nth-child(3) {
  background-color: #F00;
}

/* Edit mode */
.handsontableInput {
  background-color: #F00!important;
}
```

### Selection

​        **Background**      

```css
.handsontable td.area {
  background-color: #F00;
}
```

### Notice

​        Be careful when using Handsontable with popular CSS frameworks.        They not only modify the style of all DOM elements, including textareas and inputs,        but also add some transition properties which may negatively affect the performance.        Make sure you add styles carefully and selectively or use the official Bootstrap integration.      

​        The selection border color has been hard-coded. It can be changed using JavaScript, or alternatively        you can postscript your CSS values with an (ugly) "!important" rule.      

​        **JavaScript**      

```css
var borders = document.querySelectorAll('.handsontable .wtBorder');
for (var i = 0; i < borders.length; i++) {
  borders[i].style.backgroundColor = 'red';
}

```

​        **CSS**      

```
.wtBorder {
  background-color: #F00!important;
}
```



## 开发者指南

### 搜索 

初始化时在`option`里可以配置`search: true`开启。

```javascript
var queryResult = hot.search.query(value);
```

queryResult 格式：

```javascript
{
  {row: 0, col:0, data: "xxxxx"}，
  {row: x, col:y, data: "xxxxx"},
  length: n
}
```



###  单元格数据类型(*to be updated*)

`columns`参数详解

```javascript
hot = new Handsontable(, {
  columns: [
    xxx： yyy
  ]
});
/////////////////////
data // 在获取hot数据时，作为数据的下角标
type // 数据类型
// 其他使用详见下面例子
```

初始化的`options`中配置举例



[autocomplete](https://docs.handsontable.com/0.35.0/demo-autocomplete.html)

```javascript
hot = new Handsontable(container, {
  columns: [
    {
      type: 'numeric',
      numericFormat: {
      pattern: '$0,0.00',
      culture: 'en-US' // this is the default culture, set up for USD  # zh-CN
      },
      allowEmpty: false
    },
    {
      type: 'date',
      dateFormat: 'MM/DD/YYYY',
      correctFormat: true,
      defaultDate: '01/01/1900',
    },
```

> 时间格式列的数据可能不合法，记得在初始化后调用`hot.validateCells()`方法。

```javascript
    {
     type: 'time',
      timeFormat: 'h:mm:ss a',
      correctFormat: true
    },
    {
      type: 'checkbox'
    }，
    {
      editer: 'select',
      selectOptions:['互联网','IMS'],
    },
    {
      type: 'dropdown',
      source: ['yellow', 'red', 'orange', 'green']
    }，
```
> Internally, cell `{type: "dropdown"}` is equivalent to cell` {type: "autocomplete", strict: true, filter: false}`. Therefore you can think of `dropdown` as a searchable `<select>`.

``` javascript
    {
      type: 'autocomplete',
      source: ['yellow', 'red', 'orange and another color', 'green', 'blue', 'gray', 'black', 'white', 'purple', 'lime', 'olive', 'cyan'],
      visibleRows: 4,           // 下拉菜单可见列数
      strict: false,            // 是否严格模式（是否验证）
      trimDropdown: false,      // 宽度自适应下拉内容
      allowInvalid: true        // 严格模式开启时，是否允许存在非法值
    },
    {
      type: 'handsontable',
      handsontable: {
        colHeaders: ['Marque', 'Country', 'Parent company'],
        autoColumnSize: true,
        data: manufacturerData,
        getValue: function() {
          var selection = this.getSelected();
          // Get always manufacture name of clicked row
          return this.getSourceDataAtRow(selection[0]).name;
       },
      }
    },
  ]
});
hot.validateCells();
```

### 右键菜单 

初始化时在`option`里可以配置`contextMenu:ture`开启，或者配置为如下选项：

如：`contextMenu：[row_above,row_below,undo,redo]`

- row_above
- row_below
- hsep1
- col_left
- col_right
- hsep2
- remove_row
- remove_col
- hsep3
- undo
- redo
- make_read_only
- alignment
- borders (with Custom Borders turned on)
- commentsAddEdit, commentsRemove (with Comments turned on)

[自定义菜单](https://docs.handsontable.com/pro/1.15.0/demo-context-menu.html#page-custom)：

``` javascript
var
    example3 = document.getElementById('example3'),
    settings3,
    hot3;

  settings3 = {
    data: getData(),
    rowHeaders: true,
    colHeaders: true
  };
  hot3 = new Handsontable(example3, settings3);

  hot3.updateSettings({
    contextMenu: {
      callback: function (key, options) {
        if (key === 'about') {
          setTimeout(function () {
            // timeout is used to make sure the menu collapsed before alert is shown
            alert("This is a context menu with default and custom options mixed");
          }, 100);
        }
      },
      items: {
        "row_above": {
          disabled: function () {
            // if first row, disable this option
            return hot3.getSelected()[0] === 0;
          }
        },
        "row_below": {},
        "hsep1": "---------",
        "remove_row": {
          name: 'Remove this row, ok?',
          disabled: function () {
            // if first row, disable this option
            return hot3.getSelected()[0] === 0
          }
        },
        "hsep2": "---------",
        "about": {name: 'About this menu'}
      }
    }
  })
```

## 插件

### 获取自动调整的宽度

```javascript
// ...
hot.updateSettings({autoColumnSize:true}); 
// ...
// Access to plugin instance:
var colSizePlugin = hot.getPlugin('autoColumnSize');
var result="";
for(var ii=0;ii<30;ii++){
    result+=colSizePlugin.getColumnWidth(ii)+",";
}
console.info(result);
```

### 导出到文件

```javascript
var exportPlugin = hot.getPlugin('exportFile');

// Export as a string:
exportPlugin.exportAsString('csv');

// Export as a Blob object:
exportPlugin.exportAsBlob('csv');

// Export to downloadable file (MyFile.csv):
exportPlugin.downloadFile('csv', {filename: 'MyFile'});
```



### 