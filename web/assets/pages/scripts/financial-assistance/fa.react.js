var FinancialAssistanceComponent = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showClosingModal : false,
            activeTable : "TRANSACTIONS"
        }
    },

    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    setActiveTable: function (e) {
        this.setState({ activeTable :  e.target.value });
    },

    reload: function () {
        if(this.state.activeTable == 'TRANSACTIONS'){
            this.refs.transactionTable.reload();
        }else if(this.state.activeTable == 'DAILY_SUMMARY'){
            this.refs.summaryTable.reload();
        }
    },

    openCreateModal: function () {
        this.setState({ showCreateModal: true });
    },

    openClosingModal: function () {
        this.setState({ showClosingModal: true });
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false, target: null });
    },
    
    closeClosingModal : function(){
        this.setState({ showClosingModal : false });
    },

    render: function () {
        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body">

                {
                    this.state.showCreateModal &&
                    <FinancialAssistanceCreateModal
                        proId={3}
                        show={this.state.showCreateModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeCreateModal}
                    />
                }

                {
                    this.state.showClosingModal &&
                    <FinancialAssistanceClosingModal
                        proId={3}
                        show={this.state.showClosingModal}
                        notify={this.props.notify}
                        reload={this.reload}
                        onHide={this.closeClosingModal}
                    />
                }


                    <div className="row">
                        <div className="col-md-10">
                            <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>New Assistance</button>
                            <button type="button" className="btn btn-primary" style={{ marginLeft: "10px" }} onClick={this.openClosingModal}>Close Transactions</button>
                        </div>
                        <div className="col-md-2">
                            <select className="form-control" onChange={this.setActiveTable} value={this.state.activeTable} name="activeTable">
                                <option value="TRANSACTIONS">Transactions</option>
                                <option value="DAILY_SUMMARY">Daily Summary</option>
                            </select>
                        </div>
                    </div>

                   {
                    this.state.activeTable == "TRANSACTIONS" ? 
                    <FinancialAssistanceDatatable ref="transactionTable" notify={this.notify} /> : <FinancialAssistanceDailySummaryDatatable ref="summaryTable" notify={this.notify} />
                   }

                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <FinancialAssistanceComponent />,
        document.getElementById('page-container')
    );
}, 500);
