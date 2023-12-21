var BcbpComponent = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showSmsModal: false,
            form: {
                data: {}
            }
        }
    },

    openSmsModal: function () {
        console.log("open sms modal");
        this.setState({ showSmsModal: true });
        console.log(this.state.showSmsModal);
    },

    closeSmsModal: function () {
        this.setState({ showSmsModal: false });
    },

    openCreateModal: function () {
        console.log("open sms modal");
        this.setState({ showCreateModal: true });
        console.log(this.state.showCreateModal);
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false });
        this.refs.bcbpDatatableRef.reload();
    },

    onCreateSuccess : function(){
        this.refs.bcbpDatatableRef.reload();
    },

    render: function () {
        var self = this;

        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body" id="bcbp_component">
                    <div className="row">

                        {
                            this.state.showSmsModal &&
                            <BcbpSmsModal
                                show={this.state.showSmsModal}
                                onHide={this.closeSmsModal}
                            />
                        }

                        {
                            this.state.showCreateModal &&
                            <BcbpCreateModal
                                show={this.state.showCreateModal}
                                onHide={this.closeCreateModal}
                                onSuccess={this.onCreateSuccess}
                            />
                        }

                        <div className="col-md-12">
                            <button type="button" className="btn btn-success btn-sm" style={{ marginRight:"10px" }} onClick={this.openCreateModal}>Add Profile</button>
                            <button type="button" className="btn btn-success btn-sm" onClick={this.openSmsModal}>Send Messages</button>
                        </div>
                    </div>
                    <div className="row">
                        <BcbpDatatable ref="bcbpDatatableRef" />
                    </div>
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <BcbpComponent />,
        document.getElementById('page-container')
    );
}, 500);
