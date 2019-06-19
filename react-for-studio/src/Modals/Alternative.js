import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import Loading from './Loading';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import JsonInput from './include/Alternative/JsonInput';
import Op1 from './include/Alternative/Op1';
import Op2 from './include/Alternative/Op2';
import Cases from './include/Alternative/Cases';
import Util from '../library/utils/Util';

/* global global_data */

@inject('opsStore', 'alterStore')
@observer
class Alternative extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
    }

    componentDidMount() {
        const { selected } = this.props.opsStore;

        const lastOp =
            selected.length > 0
                ? typeof this.props.data === 'undefined'
                    ? _.last(selected).op_id
                    : this.props.data.lastOp
                : null;
        this.props.alterStore.getAlterData(lastOp).then(() => {
            const hasData =
                typeof this.props.data === 'undefined' ? false : true;
            if (hasData) {
                const { ops, base } = this.props.data;
                this.props.alterStore.setAlterSetData(base, ops);
            } else {
                this.props.alterStore.initAlterData();
            }
        });
    }

    closeModal() {
        this.props.alterStore.unsetAlter();
    }
    onChangeDesc = e => {
        this.props.alterStore.changeAlterData('desc', e.target.value);
    };
    saveAlter = e => {
        e.preventDefault();
        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            this.props.alterStore.saveAlterData();
        }
    };

    getCaseView = () => {
        const c = this.props.alterStore.setData.cases;
        const ops = this.props.data ? this.props.data.ops : null,
            l = c.length - 1;
        const addOpts = ops
            ? ops.map(op => {
                  return _.find(this.props.opsStore.selected, {
                      op_id: op.op_id,
                  });
              })
            : null;
        return <Cases cases={c} len={l} addOpts={addOpts} />;
    };

    render() {
        const { dictionary } = global_data;
        const d = this.props.alterStore.getData;
        const {
            opt1,
            opt2,
            json,
            jsonPath,
            desc,
        } = this.props.alterStore.setData;
        console.log('alt render', this.props.alterStore.setData, d);
        return (
            <>
                {d !== null ? (
                    <ModalWrapper>
                        <form
                            className="modal-content"
                            ref={this.formEl}
                            onSubmit={this.saveAlter}>
                            <ModalHeader
                                closeModal={this.closeModal.bind(this)}>
                                <i className="fa fa-lg fa-fw fa-exchange" />{' '}
                                Alternative Condition
                            </ModalHeader>
                            <div className="modal-body">
                                <div className="widget-body no-padding">
                                    <div className="smart-form">
                                        <fieldset className="fieldset-input">
                                            <section>
                                                <label className="label">
                                                    <span className="text-danger">
                                                        *
                                                    </span>{' '}
                                                    Switch
                                                </label>
                                            </section>
                                            <section className="req-data-frm">
                                                <div className="row">
                                                    <div className="col col-3">
                                                        <Op1 opt1={opt1} />
                                                    </div>
                                                    {!!opt1 ? (
                                                        <div className="col col-3">
                                                            <Op2
                                                                opt1={opt1}
                                                                opt2={opt2}
                                                            />
                                                        </div>
                                                    ) : (
                                                        ''
                                                    )}
                                                    {!!json ? (
                                                        <JsonInput
                                                            json={json}
                                                            jsonPath={jsonPath}
                                                        />
                                                    ) : (
                                                        ''
                                                    )}
                                                </div>
                                            </section>
                                            {!!opt1 && !!opt2 ? (
                                                <>
                                                    <section>
                                                        <label className="label">
                                                            <span className="text-danger">
                                                                *
                                                            </span>{' '}
                                                            case
                                                        </label>
                                                    </section>
                                                    {this.getCaseView()}
                                                </>
                                            ) : (
                                                ''
                                            )}
                                            <section>
                                                <label className="label">
                                                    <span className="text-">
                                                        *
                                                    </span>{' '}
                                                    Description
                                                </label>
                                            </section>
                                            <section className="req-data-frm">
                                                <div className="row">
                                                    <div className="col col-3">
                                                        <label className="input">
                                                            <input
                                                                type="text"
                                                                maxLength="100"
                                                                autoComplete="off"
                                                                placeholder="Value"
                                                                onChange={
                                                                    this
                                                                        .onChangeDesc
                                                                }
                                                                value={desc}
                                                                required="required"
                                                            />
                                                        </label>
                                                    </div>
                                                </div>
                                            </section>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                            <ModalFooter
                                closeModal={this.closeModal.bind(this)}>
                                {!!opt1 && !!opt2 ? (
                                    <button
                                        type="submit"
                                        className="btn btn-primary">
                                        <i className="fa fa-plus" />{' '}
                                        {dictionary.button.save}
                                    </button>
                                ) : (
                                    ''
                                )}
                            </ModalFooter>
                        </form>
                    </ModalWrapper>
                ) : (
                    <Loading />
                )}
            </>
        );
    }
}

export default Alternative;
