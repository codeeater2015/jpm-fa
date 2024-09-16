var KamadaComponent = React.createClass({
    render : function(){
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                   <KamadaDatatable/>
                </div>
            </div>
        )
    }
});

setTimeout(function(){
    ReactDOM.render(
    <KamadaComponent />,
        document.getElementById('page-container')
    );
},500);
