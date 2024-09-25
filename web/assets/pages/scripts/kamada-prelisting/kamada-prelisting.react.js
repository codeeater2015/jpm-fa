var KamadaPrelistingComponent = React.createClass({
    render : function(){
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <KamadaPrelistingDatatable/>
                </div>
            </div>
        )
    }
});

setTimeout(function(){
    ReactDOM.render(
    <KamadaPrelistingComponent />,
        document.getElementById('page-container')
    );
},500);
