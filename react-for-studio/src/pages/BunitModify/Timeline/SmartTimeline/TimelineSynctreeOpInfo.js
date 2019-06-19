import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import AlterExplain from './AlterExplain';
import BtnNormal from './opBtns/BtnNormal';
import BtnDisabled from './opBtns/BtnDisabled';
import BtnAsync from './opBtns/BtnAsync';
import Arrows from './Arrows';

@inject('bizStore', 'alterStore')
@observer
class TimelineSynctreeOpInfo extends Component {
    render() {
        const {
            row,
            index,
            prevOpIdx,
            opIdx,
            bindingSeq,
            controllInfo,
        } = this.props;

        const { asyncMode } = this.props.bizStore;

        const midxs = [];
        if (prevOpIdx !== null) {
            midxs.push(prevOpIdx);
        }
        midxs.push(opIdx);
        const explain =
            controllInfo && this.props.alterStore.controlOperator ? (
                <AlterExplain
                    controllInfo={controllInfo}
                    controlOperator={this.props.alterStore.controlOperator}
                />
            ) : (
                ''
            );

        const btn = asyncMode ? (
            !!controllInfo ? (
                <BtnDisabled data={row} />
            ) : (
                <BtnAsync data={row} />
            )
        ) : (
            <BtnNormal
                data={row}
                controllInfo={controllInfo}
                midxs={midxs}
                bindingSeq={bindingSeq}
                index={index}
                opIdx={opIdx}
            />
        );

        return (
            <section className="sec-unit declare-op">
                {btn}
                {explain}
                <Arrows index={index} />
            </section>
        );
    }
}
export default TimelineSynctreeOpInfo;
