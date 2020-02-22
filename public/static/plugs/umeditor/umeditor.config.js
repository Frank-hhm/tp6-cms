
(function () {

    /**
     * 配置项主体。注意，此处所有涉及到路径的配置别遗漏URL变量。
     */
    window.UMEDITOR_CONFIG = {

        //为编辑器实例添加一个路径，这个不能被注释
        UMEDITOR_HOME_URL: ROOT_URL+ "/static/plugs/umeditor/"      

        //图片上传配置区
        , imageUrl:  ROOT_URL + "/upload/image"             //图片上传提交地址
        , imagePath: URL + "php/"                     //图片修正地址，引用了fixedImagePath,如有特殊需求，可自行配置
        , imageFieldName: "iFile"                   //图片数据的key,若此处修改，需要在后台对应文件修改对应参数
        , toolbar: [
            'source | undo redo | bold italic underline strikethrough | superscript subscript | forecolor backcolor | removeformat |',
            'insertorderedlist insertunorderedlist | selectall paragraph | fontfamily fontsize',
            '| justifyleft justifycenter justifyright justifyjustify ',
            '| image insertimage video',
            '| horizontal'
        ]
        ,charset:"utf-8"
        ,imageScaleEnabled: false
        ,autoHeightEnabled: false
        , autoFloatEnabled: false
        , topOffset: 51
    };
})();
