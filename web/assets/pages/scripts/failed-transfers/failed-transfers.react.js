var FailedTransferPage = React.createClass({
    render : function(){
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">
                    <FailedTransferDatatable/>
                </div>
            </div>
        )
    }
});

setTimeout(function(){
    ReactDOM.render(
    <FailedTransferPage />,
        document.getElementById('page-container')
    );
},500);
