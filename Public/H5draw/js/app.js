/**
 * 动态标绘API plot4ol3，基于OpenLayer3开发，旨在为基于开源GIS技术做项目开发提供标绘API。
 * 当前版本1.0，提供的功能：绘制基本标绘符号。
 * 绘制接口: PlotDraw
 * 编辑接口: PlotEdit
 * 具体用法请参考演示系统源码。
 *
 * 开发者：@平凡的世界
 * QQ号：21587252
 * 邮箱：gispace@yeah.net
 * 博客：http://blog.csdn.net/gispace
 * 动态标绘交流QQ群：318659439
 *
 * 如果想要收到API更新消息，请开源项目页面评论中留下联系方式 http://git.oschina.net/ilocation/plot
 *
 * */
var map, plotDraw, plotEdit, drawOverlay, drawStyle,labelFlag,DrawType,_layer;
//var isFirst=true;//是否第一次加载该canvas图层
var canvasOption=new Object();
//ImageCanvas有一个canvasFunction属性
//通过查看源代码，发现该属性其实是一个回调函数，需要对该函数进行实现，从而创建一个canvas
//创建回调函数如下
canvasOption.canvasFunction=function(extent, resolution, pixelRatio, size, projection){
    if(typeof(canvas) == 'undefined')//这里必须要做一个判断，每次的范围变动都会引起重绘，从而触发该回调函数，不判断的话，将会导致canvas无法被绘制到地图上，出现闪现的情况
    {
        //isFirst=false;
        canvas=document.createElement('canvas');
        canvas.width=size[0];
        canvas.height=size[1];
        var mapExtent = map.getView().calculateExtent(map.getSize());
        var canvasOrigin = map.getPixelFromCoordinate([extent[0], extent[3]]);
        var mapOrigin = map.getPixelFromCoordinate([mapExtent[0], mapExtent[3]]);
        var delta = [mapOrigin[0]-canvasOrigin[0], mapOrigin[1]-canvasOrigin[1]];
        var point = ol.proj.transform([118.51, 39.53], 'EPSG:4326', 'EPSG:3857');
        var pixel = map.getPixelFromCoordinate(point);
        var cX = pixel[0] + delta[0], cY = pixel[1] + delta[1];

        var context = canvas.getContext('2d');
            context.fillStyle = "rgba(0,0,0,0.1)";
            context.fillRect(cX,cY,500,500);
        var yImg = new Image();
        yImg.setAttribute('crossOrigin', 'anonymous');
        yImg.src = 'land25d.png';
        yImg.onload = function(){
            context.drawImage(yImg,cX,cY,500,500);
        };
        return canvas;
    }
};
//添加网格
var graticuleLayer = new ol.Graticule({
    // map: map,
    strokeStyle: new ol.style.Stroke({
        color: 'rgba(147,184,230, 0.8)',
        width: 0.6
    }),
    targetSize: 100
});
//为ImageCanvasLayer创建数据源
var imageCanvas=new ol.source.ImageCanvas(canvasOption);
//创建一个ImageCanvasLayer图层
var imageCanvasLayer=new ol.layer.Image({
        source:imageCanvas,
    });
//imageCanvasLayer.setZIndex(0);
//console.log(imageCanvasLayer);
function init(){
    //初始化地图
    View= new ol.View({
        center: ol.proj.transform([122.51, 37.53], 'EPSG:4326', 'EPSG:3857'),
        zoom: 7
    })
    sd = ol.proj.fromLonLat([122.51, 37.53]);
    $("#toArea").click( function() {
        View.setZoom(7);
        View.setCenter(sd);
    });
    var scaleLineControl = new ol.control.ScaleLine();
    map = new ol.Map({
        target: 'map',
        layers: [
            new ol.layer.Tile({
                //source: new ol.source.MapQuest({layer: 'sat'})
                source: new ol.source.XYZ({  
                    url:'http://www.google.cn/maps/vt/pb=!1m4!1m3!1i{z}!2i{x}!3i{y}!2m3!1e0!2sm!3i345013117!3m8!2szh-CN!3scn!5e1105!12m4!1e68!2m2!1sset!2sRoadmap!4e0',
                    crossOrigin:'anonymous'
                })
            }),
            //imageCanvasLayer,
        ],
        view:View,
        controls: new ol.control.defaults().extend([new ol.control.MousePosition({
            projection: 'EPSG:4326',
            coordinateFormat: ol.coordinate.createStringXY(4),
        })]).extend([
            scaleLineControl
        ])
    });

    map.on('click', function(e){
        //console.log(plotDraw);
        var point = e.coordinate; //鼠标单击点坐标
        if(labelFlag){
            addOverlayLabel(DrawType,point);//添加新的图文标注（overlay标注）
            return;
        }
        if(plotDraw.isDrawing()){
            return;
        }
        _layer = map.forEachLayerAtPixel(e.pixel, function (feature, layer) {
            console.log(feature);
            return feature;
        });
        var feature = map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
            return feature;
        });
        if(feature){
            // 开始编辑
            plotEdit.activate(feature);
            setProp(feature);
            //activeDelBtn();
        }else{
            // 结束编辑
            plotEdit.deactivate();
            //deactiveDelBtn();
            layer.closeAll();
        }
    });
    map.on('moveend',function(e){
        var point = ol.proj.transform([118.51, 39.53], 'EPSG:4326', 'EPSG:3857');
        pixel = map.getPixelFromCoordinate(point);
        //console.log(pixel);
    });
    // 初始化标绘绘制工具，添加绘制结束事件响应
    plotDraw = new P.PlotDraw(map);
    plotDraw.on(P.Event.PlotDrawEvent.DRAW_END, onDrawEnd, false, this);

    // 初始化标绘编辑工具
    plotEdit = new P.PlotEdit(map);

    // 设置标绘符号显示的默认样式
    var stroke = new ol.style.Stroke({color: '#FF0000', width: 2});
    var fill = new ol.style.Fill({color: 'rgba(0,255,0,1)'});
    var image = new ol.style.Circle({fill: fill, stroke: stroke, radius: 8});
    drawStyle = new ol.style.Style({image: image, fill:fill, stroke:stroke});

    // 绘制好的标绘符号，添加到FeatureOverlay显示。
    drawOverlay = new ol.layer.Vector({
        source: new ol.source.Vector()
    });
    //drawOverlay.setZIndex(0);
    graticuleLayer.setMap(map);
    drawOverlay.setStyle(drawStyle);
    drawOverlay.setMap(map);
    //imageCanvasLayer.setPosition(sd);
    imageCanvasLayer.setMap(map);
    //get('btn-delete').onclick = function(){
    //    if(drawOverlay && plotEdit && plotEdit.activePlot){
    //        drawOverlay.getSource().removeFeature(plotEdit.activePlot);
    //        plotEdit.deactivate();
    //        //deactiveDelBtn();
    //        //console.log(editWindow);
    //        layer.closeAll();
    //    }
    //};
}

// 绘制结束后，添加到FeatureOverlay显示。
function onDrawEnd(event){
    var feature = event.feature;
    drawOverlay.getSource().addFeature(feature);
    setProp(feature);
    // 开始编辑
    plotEdit.activate(feature);
    //activeDelBtn();
}

// 指定标绘类型，开始绘制。
function activate(type){
    layer.closeAll();
    plotEdit.deactivate();
    plotDraw.activate(type);
};
function OverlayLabels(Type){
    labelFlag=true;
    DrawType = Type;
}
var id=0;
function addOverlayLabel(DrawType,coordinate) {
    labelFlag=false;
    if(DrawType == 'word'){
        var elementA = document.createElement("input");
        elementA.className = "label";
        elementA.size = 10;
        elementA.value = "请输入标记";
        elementA.style.background='none';
        elementA.style.border='1px solid #000';
        elementA.style.outline='none';
        elementA.id = id;
    }else if(DrawType == 'flag'){
        var elementA = document.createElement("img");
        elementA.className = "label";
        elementA.id = id;
        elementA.width = 24;
        elementA.src = '/H5draw/assets/pencil.png'
    }
    var newText = new ol.Overlay({
        position: coordinate,
        element: elementA,
        id: id++
    });
    map.addOverlay(newText);
    var olayer = map.getOverlayById(id-1);
    $('#'+(id-1)).parent().draggabilly().on( 'dragStart', function( event, pointer ) {
        var x = pointer.x;
        var y = pointer.y;
        console.log(pointer);
        var coord = map.getCoordinateFromPixel([x,y]);
        olayer.setPosition(coord);
    })
    $('#'+(id-1)).parent().draggabilly().on( 'dragEnd', function( event, pointer ) {
        var x = pointer.x;
        var y = pointer.y;
        console.log(event);
        var coord = map.getCoordinateFromPixel([x,y]);
        console.log(ol.proj.transform(olayer.getPosition(), 'EPSG:4326', 'EPSG:900913'));

        olayer.setPosition(coord);
    })
}
function showAbout(){
    document.getElementById("aboutContainer").style.visibility = "visible";
}

function hideAbout(){
    document.getElementById("aboutContainer").style.visibility = "hidden";
}

function get(domId){
    return document.getElementById(domId);
}

//function activeDelBtn(){
//    get('btn-delete').style.display = 'inline-block';
//}
//
//function deactiveDelBtn(){
//    get('btn-delete').style.display = 'none';
//}
$("#save").click(function(){
    //View.setZoom(7);
    //View.setCenter(sd);
    saveDraw()
})
$("#save1").click(function(){
    View.setZoom(7);
    View.setCenter(sd);
    setTimeout(saveDraw1,1500);
})
function saveDraw(){
    html2canvas(document.getElementById('map')).then(function(canvas) {
        //document.body.appendChild(canvas);
        var reg=canvas.toDataURL("image/png");//跳转页面手动保存
        //window.open('/saveDraw.html?img='+reg,'_blank');
        $.ajax({
            url:"http://192.168.0.110:82/hpwebservice/?service=Default.saveimg",
            type:"post",
            dataType:"json",
            crossDomain: true,
            data:{
                img:reg
            },
            success:function(response){
                $("#imgUrl").val(response.data.imgUrl);
                $("#drawForm").submit();
            }
        })

    });
}
function saveDraw1(){
    html2canvas(document.getElementById('map')).then(function(canvas) {
        //document.body.appendChild(canvas);
        var reg=canvas.toDataURL("image/png");//跳转页面手动保存
        //window.open('/saveDraw.html?img='+reg,'_blank');
        $.ajax({
            url:"http://192.168.0.110:82/hpwebservice/?service=Default.saveimg1",
            type:"post",
            dataType:"json",
            crossDomain: true,
            data:{
                img:reg,
                pixel:pixel
            },

            success:function(response){

            }
        })

    });
}
function setProp(feature){
    layer.closeAll();
    layer.open({
        type: 1,
        title:"设置属性",
        area: ['250px','300px'],
        offset: 'r',
        shade: 0,
        btnAlign: 'c',
        btn: ['确定', '删除']
        ,yes: function(index, layero){
            var width = Number($("#StrokeWidth").val());

            var stroke = new ol.style.Stroke({color: $("#StrokeColor").val(), width: width});
            var fill = new ol.style.Fill({color: $("#Fill").val()});
            var image = new ol.style.Circle({fill: fill, stroke: stroke, radius: 8});
            var drawStyle = new ol.style.Style({image: image, fill:fill, stroke:stroke});
            feature.setStyle(drawStyle);
        },btn2: function(index, layero){
            if(drawOverlay && plotEdit && plotEdit.activePlot){
                drawOverlay.getSource().removeFeature(plotEdit.activePlot);
                plotEdit.deactivate();
                layer.closeAll();
            }
        },
        //shadeClose:true,
        content: $("#proparea"),
        success:function(){
        }
    });
}
$('body').on('keydown','input',function(){
    this.size=this.value.length>4?this.value.length+10:4;
})
$('body').on('focus',".label",function(){
    var self=$(this);
    layer.closeAll();
    layer.open({
        type: 1,
        title:"设置属性",
        area: ['250px','300px'],
        offset: 'r',
        shade: 0,
        btnAlign: 'c',
        btn: ['确定', '删除']
        ,yes: function(index, layero){
            self.css({
                fontSize:$("#fontSize").val()+'px',
                color:$("#fontColor").val(),
                border:$("#outline").val()
            })
        },btn2: function(index, layero){
            self.parent().remove();
        },
        //shadeClose:true,
        content: $("#propinput"),
        success:function(){
        }
    });
})