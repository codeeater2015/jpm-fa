var KfcAttendance = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showSmsModal: false,
            form: {
                data: {}
            }
        }
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

    onCreateSuccess: function () {
        this.refs.attendanceDatatable.reload();
        this.setState({showCreateModal : false});
    },

    render: function () {
        var self = this;

        return (
            <div className="portlet light portlet-fit bordered">
                {
                    this.state.showCreateModal &&
                    <KfcAttendanceCreateModal
                        show={this.state.showCreateModal}
                        onHide={this.closeCreateModal}
                        onSuccess={this.onCreateSuccess}
                    />
                }
                
                <div className="portlet-body" id="bcbp_component">
                    <div className="row">
                        <div className="col-md-12">
                            <button type="button" className="btn btn-success btn-sm" style={{ marginRight: "10px" }} onClick={this.openCreateModal}>Add Attendance</button>
                        </div>
                    </div>
                    <div className="row">
                        <KfcAttendanceDatatable ref="attendanceDatatable" />
                    </div>
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <KfcAttendance />,
        document.getElementById('page-container')
    );
}, 500);
