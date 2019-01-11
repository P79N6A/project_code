<div class="xieyidm">
    <h3>先花一亿元居间服务及借款协议（三方）<br/><span>（“本协议”）<span style="color:#e74747;">由以下各方于【  <?php echo date('Y', strtotime($loaninfo['create_time'])); ?> 】年【 <?php echo date('m', strtotime($loaninfo['create_time'])); ?>  】月【 <?php echo date('d', strtotime($loaninfo['create_time'])); ?>  】日签订： </span></span></h3>
    <p class="mb20">甲方 (借款方)：<span><?php echo $loaninfo['realname']; ?></span></p>
    <p class="mb20">身份证号码：<span><?php echo $loaninfo['identity']; ?></span></p>

    <p class="mb20">乙方 (出借人/投资人)：<span></span></p>
    <?php if (!empty($contributorarr)): ?>
    <table border="1">
        <thead>
        <tr>
            <th>姓名</th>
            <th>身份证号</th>
            <th>投资金额</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $amout = $loaninfo['amount'];
        $count = count($contributorarr)-1;
        foreach ($contributorarr as $k => $v) {
            $amout = $amout-$v['money'];
            if($k==$count){
                $v['money'] += $amout;
            }
            echo "
                <tr>
                    <td>".$v['realname']."</td>
                    <td>".$v['identity']."</td>
                    <td>".sprintf('%.2f', $v['money'])."</td>
                </tr>
            ";
        }
        ?>
        </tbody>
    </table>
    <?php endif; ?>
    <p>丙方：<b>先花信息技术（北京）有限公司</b><br/>（拥有先花一亿元微信公众号[*]的经营权。）</p>
    <p>住所：<b>北京市海淀区北四环西路65号海淀新技术大厦南门10层1000室</b></p>

    <p class="mb20">鉴于：</p>
    <p class="mb20">1. 甲方有一定的资金需求，丙方有丰富的出借人资源信息和一套信用管理服务系统，可为用户提供借款信息发布和撮合服务（信息发布平台包括但不限于丙方平台或丙方的关联公司及丙方展开个人借款业务的合作伙伴平台等）。</p>
    <p class="mb20">2. 甲方愿意接受丙方提供的信用咨询与管理服务，由丙方撮合甲方与乙方达成借贷交易，丙方愿意向甲方提供该等服务。</p>
    <p class="mb20">因此，三方根据平等、自愿的原则，达成本协议如下：</p>
    <p class="mb20"><strong>一、借款及还款</strong></p>
    <p class="mb20"><strong>1. 定义：</strong></p>
    <p class="mb20">在本协议中，除在文中定义的词语以及丙方不时在本微信公众号公布的有关定义与释义规则中定义的词语外，除非本协议另有规定，以下词语在本协议中定义如下：</p>
    <p class="mb20">a. 出借人/投资人：指在丙方平台或丙方合作机构成功注册账户的会员，通过丙方平台撮合推荐，可自主选择出借一定数量资金给借款人，且具有完全民事权利/行为能力的自然人。</p>
    <p class="mb20">b. 借款人：指有一定的资金需求，在丙方平台成功注册的会员，通过丙方平台得到投资人资金，且具有完全民事权利/行为能力的自然人。</p>
    <p class="mb20">c. 先花一亿元账户：指借款人以自身名义在丙方平台注册后系统自动产生的、开设于丙方的虚拟账户，可以通过第三方支付机构及/或其他通道进行充值或提现。</p>
    <p class="mb20">d. 先花一亿元平台：指由丙方运营的微信公众号“先花一亿元”和手机App软件“先花一亿元”（以下简称”丙方平台”）。</p>
    <p class="mb20">e. [服务费：包括但不限于丙方依照本协议约定应向乙方支付的收益以及丙方提供本协议项下服务应当收取费用。]</p>
    <p class="mb20">f. 甲方基本信息：指甲方为使用丙方平台服务而应向丙方提供的包括但不限于姓名、年龄、性别、身份证号码、身份证正面照、身份证有效期、电子邮件地址、联系电话、联系地址、本人银行账户信息、教育信息、工作信息、社交账号、在丙方平台上使用的昵称及其他个人身份信息等信息。</p>
    <p class="mb20">g. 甲方信用信息：指丙方从甲方处获得的以及通过第三方渠道获得的甲方的除甲方基本信息之外的其他有关甲方资信状况的信息，如甲方的工作情况、收入情况、家庭情况、信用情况、历史偿债情况、违法情况等，以及甲方使用丙方平台的历史记录和由此形成的信用数据。</p>

    <p class="mb20">h. 甲方信息：指甲方基本信息、甲方信用信息以及丙方根据该等信息及丙方内部的评级规则对借款人做出的信用评级的合称。</p>

    <p class="mb20">i. 借款协议：指经丙方居间而由甲方与出借人以电子合同的形式订立的、约定甲方向出借人借入一定数额的出借资金，甲方到期还本付息的借款协议。</p>
    <p class="mb20">j. 债务：指在借款协议项下甲方所负有的全部本金、服务费、逾期罚息等债务，以人民币计价。    </p>
    <p class="mb20"><strong>2. 借款金额及期限</strong></p>
    <p class="mb20">甲方同意通过本微信公众号向乙方借款如下，乙方同意通过本微信公众号向甲方发放该等借款：</p>
    <p class="mb20">&nbsp;</p>
    <p class="mb20">&nbsp;</p>
    <table border="1">
        <thead>
            <tr>
                <th>借款详细用途</th>
                <th><?php echo $loaninfo['desc']; ?></th>
                <th>利息</th>
                <th>18%/年</th>
                <th>十万</th>
                <th>万</th>
                <th>千</th>
                <th>百</th>
                <th>十</th>
                <th>元</th>
                <th>角</th>
                <th>分</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>借款本金数额</td>
                <td colspan="2" >人民币(大写)</td>
                <td>&nbsp;</td>
                <?php
                foreach ($daxie_loan_amount_num as $k => $v) {
                    echo "<td>" . $v . "</td>";
                }
                ?>
            </tr>
            <tr>
                <td>到期偿还本息数额</td>
                <td colspan="2" >人民币(大写)</td>
                <td>&nbsp;</td>
                <?php
                foreach ($daxie_endamount_num as $k => $v) {
                    echo "<td>" . $v . "</td>";
                }
                ?>
            </tr>
            <tr>
                <td>还款周期</td>
                <td >_<?php echo $loaninfo['days']; ?>__ 天</td>
                <td colspan="2">&nbsp;</td>
                <td colspan="8">&nbsp;</td>
            </tr>
            <tr>
                <td>还款日期</td>
                <td colspan="11" >__<?php echo date('Y', strtotime($huankuandate)); ?>___ 年 __<?php echo date('m', strtotime($huankuandate)); ?>___ 月 __<?php echo date('d', strtotime($huankuandate)); ?>___ 日</td>

            </tr>
        </tbody>
    </table>
    <div style="clear:both;"></div>
    <p class="mb20"><strong>3. 借款流程</strong></p>
    <p class="mb20">3.1 本协议成立：乙方按照丙方平台不时发布的规则，接受丙方的撮合服务向甲方出借资金时，本协议立即成立。</p>
    <p class="mb20">3.2 3.3 出借资金划转：甲方发布的借款需求全部或部分得到满足（届时根据筹款情况确定），且甲方借款需求所对应的资金已经全部冻结后，甲方、乙方即不可撤销地授权丙方委托相应的第三方支付机构及监管银行等合作机构，将金额等同于本协议第一条所列的“借款本金数额”的资金（扣除依据届时微信公众号显示或确定的服务费和其他必要费用收取标准）划转至甲方银行账户。</p>
    <p class="mb20">3.4 付款方式</p>
    <p class="mb20">3.4.1甲方和乙方同意授权第三方支付机构或银行通过以下一种或多种方式完成借款的支付：</p>
    <p class="mb20">（1）支付至本协议列明的甲方个人银行账户；</p>
    <p class="mb20">（2）支付至借款人在丙方或丙方的合作方指定的第三方支付机构开设的虚拟账户；</p>
    <p class="mb20">（3）支付至丙方于【】银行设立的第三方托管账户，由丙方先为代收，并于丙方收到当日或双方约定的时间代付至乙方指定的收款账户（包括但不限于为借款人支付其购买商品或服务所应支付的价款等）。</p>
    <p class="mb20">（4）各方约定的其他支付方式。</p>
    <p class="mb20">3.4.2借款人和出借人均同意第三方支付机构或银行接受委托后进行的行为所产生的法律后果均由相应委托方承担。</p>
    <p class="mb20">3.5 甲方同意并授权丙方，出于向甲方提供借款及相关服务之需要（包括但不限于借款申请的处理、各个阶段的借款管理、账户及付款管理、权利的保护及相关执行等），提供其个人信息和相关借款信息给丙方、丙方的关联公司及丙方展开个人借款业务的合作伙伴平台。</p>
    <p class="mb20">3.6 甲方同意并委托丙方及其合作伙伴平台以甲方本人名义在相关借款业务系统（包括但不限于丙方、丙方合作伙伴的系统）、第三方支付公司注册账户、展示甲方的借款信息并协助甲方进行资金划付。</p>
    <p class="mb20">3.7 本协议生效：本协议在丙方发出资金划转指令时立即生效，借款服务费及相关费用开始计算。</p>
    <p class="mb20"><strong>4. 声明及保证</strong></p>
    <p class="mb20">4.1 乙方应保证其所用于出借的资金来源合法，乙方是该资金的合法所有人，如果第三方对资金归属、合法性问题提出异议，由乙方自行解决。如乙方未能解决，则放弃享有其所出借资金所带来的利息等收益。</p>
    <p class="mb20">4.2 丙方及丙方的关联方、董事、股东、员工、代理人均不以任何明示或默示的方式对您通过丙方平台形成的借贷交易及其履行提供任何担保或承诺。</p>
    <p class="mb20">4.3 乙方同意并确认，（1）为保护甲方的隐私，针对本协议项下借款，丙方无需在丙方平台上展示全部借款人基本信息，且（2）乙方不依赖具体的甲方基本信息做出借款决定，并已获得其作出本协议项下借款决定所需的所有信息。</p>
    <p class="mb20"><strong>5. 收费及税费</strong></p>
    <p class="mb20">5.1 丙方有权就为本协议借款所提供的服务向甲方及乙方收取相应费用，收取标准按照届时丙方平台显示或确定的服务费和其他必要费用的收取标准执行。</p>
    <p class="mb20">5.2 乙方应自行负担并主动缴纳因利息所得带来的可能的税费。</p>
    <p class="mb20"><strong>6. 偿还方式</strong></p>
    <p class="mb20">6.1 甲方必须按照本协议的约定按时、足额偿还对乙方的借款本金和利息，并将相应款项付至如下丙方支付宝账户：</p>
    <p class="mb20">丙方支付宝企业账户：fqzfb@xianhuahua.com</p>
    <p class="mb20">甲方授权同意丙方在本协议期限内，委托银行或第三方支付机构从本协议指定的甲方的个人银行账户内以约定的资费标准自行划付甲方应付的全部费用，甲方承诺在指定账户中留有足够余额，否则因账户余额不足或不可归责于丙方的任何事由，导致无法及时扣款或扣款错误、失败，责任由甲方自行承担。</p>
    <p class="mb20">6.2 丙方根据与乙方之间的约定通过银行或第三方支付机构向乙方支付前述约定的资费标准的全部费用。 </p>
    <p class="mb20">6.3 如果还款日遇到法定假日或公休日，还款日期不顺延；如果当月无还款日对应日期，则还款日为当月的最后一日。</p>
    <p class="mb20">6.4 若因丙方的合作机构（如第三方支付机构或银行）的原因导致丙方未能按时收到上述款项，由此产生的各种纠纷与丙方无关，甲方应按本协议约定的逾期情况，支付相应的逾期罚息及逾期违约金。若因丙方或丙方合作机构原因导致乙方未能按时收到上述款项，由此产生的纠纷由丙方自行处理。</p>
    <p class="mb20"><strong>7. 提前还款</strong></p>
    <p class="mb20">7.1 甲方提出提前偿还全部剩余本金的，甲方应承担的借款利息按原有的还款期限计算，即甲方应承担的借款利息不因提前还款而减少。提前还款的款项划转方式与第6条约定正常还款的方式相同。</p>
    <p class="mb20">7.2 若甲方存在第10.2条所述任一情形的，乙方有权宣告甲方的剩余借款提前到期并向甲方发起提前还款的要求；乙方在此不可撤销的授权丙方作为其代表宣告甲方的剩余借款提前到期并向甲方发起提前还款的要求。在前述情形下，甲方应在收到丙方发出要求后三（3）个工作日内向丙方支付宝账户划转应还款项。此时，提前还款的金额包括剩余全部本金、按原借款期限计算的应还利息与服务费（如有）。如同时存在逾期情况的，还应支付根据本协议计算的逾期罚息。</p>
    <p class="mb20">7.3 本协议项下的借款不允许提前部分还款。</p>
    <p class="mb20"><strong>8. 逾期还款</strong></p>
    <p class="mb20">8.1 如约定还款日（24:00）前甲方未向丙方提供真实有效的当期应还款的还款凭证，视为逾期还款。</p>
    <p class="mb20">8.2 发生逾期还款的，甲方应按照其本金和服务费，自逾期之日按本金和服务费的1%/日的利率按日计收逾期罚息（“逾期罚息”），直至清偿完毕之日止，逾期罚息计复利。逾期罚息由丙方收取。</p>
    <p class="mb20">8.3 如甲方逾期还款而产生的逾期责任（包括但不限于逾期罚息等）由甲方承担。</p>
    <p class="mb20">8.4 甲方未按照本协议的约定按时、足额偿还任何一期借款本息超过三（3）日的，丙方有权启动催收程序。丙方根据本协议对甲方启动催收程序时，丙方或委托第三方派出人员（至少2名）至甲方披露的住所地或经常居住地（联系地址）处催收和进行违约提醒，同时向甲方发送催收通知单，甲方应当签收，甲方不签收的，不影响上门催收提醒的进行。丙方或委托的第三方采取上门催收提醒的，甲方应当向丙方或委托的第三方支付上门提醒费用，收费标准为每次人民币1000元，此外，甲方还应向丙方支付进行上门催收提醒服务的差旅费（包括但不限于交通费、食宿费等）。</p>
    <p class="mb20"><strong>9. 借款展期</strong></p>
    <p class="mb20">9.1 在本合同生效后，约定还款日（24:00）前，甲方可以通过丙方平台提供的选择通道自主选择是否进行借款展期；对于每笔借款，甲方有权选择展期【1】次；每次展期均将借款展期延长一个本合同一.2条中约定的借款期限。</p>
    <p class="mb20">9.2 借款展期指令自甲方【确认续期后支付成功】后即视为甲方发出对此笔借款的展期要约，【由丙方负责审核此要约并提交至乙方决定，】指令发出后48小时内做出是否同意展期的反馈并通过【站内信】方式通知甲方。因甲方借款可能存在多位出借人的情形，则可获得展期的借款额度视各出借人最终决定而定。</p>
    <p class="mb20">9.3 展期费用</p>
    <p class="mb20">每次展期的对应服务费用等同于本合同一.2条中约定的手续费，甲方应在获得展期批准、丙方平台发起展期费用支付通知之时向平台支付展期服务费用、上一个借款期限的利息与手续费，方可获得展期</p>
    <p class="mb20"><strong>10. 违约责任</strong></p>
    <p class="mb20">10.1 如果甲方擅自改变本协议第一条规定的借款用途、严重违反本协议义务、提供虚假资料、故意隐瞒重要事实或未经乙方同意擅自转让本协议项下借款债务的视为甲方恶意违约，乙方有权提前终止本协议；同时，甲方应向丙方支付借款本金总额10%的金额作为恶意违约金。甲方须在乙方提出终止本协议之日起三（3）日内，向丙方银行账户或支付宝账户一次性支付余下的所有本金、利息和恶意违约金，丙方再根据其与乙方之间的约定向乙方支付该等资金。如甲方行为构成犯罪的，乙方有权向相关国家机关报案，追究甲方刑事责任。</p>
    <p class="mb20">10.2 发生下列任何一项或几项情形的，甲方应立即通知丙方，并视为甲方严重违约：</p>
    <p class="mb20">(1) 甲方的任何财产遭受没收、征用、查封、扣押、冻结等可能影响其履约能力的不利事件，且不能及时提供有效补救措施的；</p>
    <p class="mb20">(2) 甲方的财务状况出现影响其履约能力的不利变化，且不能及时提供有效补救措施的。</p>
    <p class="mb20">(3) 未按照本协议的约定按时、足额偿还任何一期借款本息超过三（3）日的。</p>
    <p class="mb20">10.3 若发生第9.2条所述情形，或根据乙方合理判断甲方可能发生第9.2条所述的违约事件的，乙方有权自行采取下列任何一项或几项救济措施：</p>
    <p class="mb20">(1) 立即暂缓、取消发放全部或部分借款；</p>
    <p class="mb20">(2) 宣布已发放借款全部提前到期，甲方应立即偿还所有应付款；</p>
    <p class="mb20">(3) 提前终止本协议；</p>
    <p class="mb20">(4) 采取法律、法规以及本协议约定的其他救济措施。</p>
    <p class="mb20">10.4 丙方保留将甲方违约失信的相关信息进行公示、计入甲方信用档案、按照法律法规的规定提供的有关政府部门或按照有关协议约定提供给第三方的权利。</p>
    <p class="mb20">10.5 因甲方违约而产生的费用（包括但不限于调查及诉讼费用）将全部由甲方承担，并应依法向乙方及/或丙方承担违约责任。</p>
    <p class="mb20"><strong>11. 变更通知</strong></p>
    <p class="mb20">本协议签订之日至借款全部清偿之日期间，若甲方未按照与丙方的约定将变更的甲方信息（包括但不限于甲方姓名、身份证号码、手机号码、联络方式、银行账户、住址、电子邮件等信息的变更）提供给丙方，或未应丙方要求提交相应的证明文件的，乙方及/或丙方的调查及诉讼费用应由甲方承担。</p>
    <p class="mb20"><strong>12. 债务转让</strong></p>
    <p class="mb20">12.1 各方同意并确认，乙方可将本协议项下全部借款的债权转让予第三方。</p>
    <p class="mb20">12.2 乙方根据本协议转让借款债权时，甲方不可撤销地授权丙方代为接收该等转让通知；债权受让人依法承接乙方在本协议项下的权利和义务。债权转让后，甲方需对债权受让人在剩余借款期限继续履行本协议下其对乙方的还款义务。</p>
    <p class="mb20">12.3 为避免发生歧义，上述债权转让不影响甲方应向丙方支付的各项款项及向丙方履行各项义务。</p>
    <p class="mb20"><strong>13. 债务转让</strong></p>
    <p class="mb20">未经乙方事先书面（包括但不限于电子邮件等方式）同意，甲方不得将本协议项下的任何权利义务转让给任何第三方。</p>
    <p class="mb20"><strong>14. 争议解决方式</strong></p>
    <p class="mb20">14.1 如果各方在本协议履行过程中发生任何争议，应友好协商解决；如协商不成，则须提交丙方所在地人民法院进行诉讼。</p>
    <p class="mb20">14.2 若甲方未按时足额履行还款义务，乙方不可撤销地授权丙方全权代表其处置该笔债权，处置的方式包括但不限于：（1）通过法律途径向甲方进行追偿（包括但不限于代为委托律师提起诉讼）；（2）自行或委托第三方专业机构进行催收；（3）将该笔债权出售予第三方资产管理公司等机构；（4）丙方认为最符合乙方利益最大化的其他处置方式。</p>
    <p class="mb20">以上处置所产生的费用由乙方承担（但如可行，丙方不应放弃由甲方承担该等费用的诉求）。</p>
    <p class="mb20">14.3 上述情况下，丙方的权限包括但不限于代为提出、承认、变更、撤回、放弃诉讼请求；代为进行答辩，提出、承认、变更、撤回、放弃诉讼反请求；参加开庭审理、陈述事实及代理意见并参加调查、质证活动；接受调解、和解；代为领取诉讼文书及诉讼标的，且该等权限可以进行转授权。</p>
    <p class="mb20"><strong>15. 其他</strong></p>
    <p class="mb20">15.1 本协议以电子文本形式生成并按第3.4条的规定生效。</p>
    <p class="mb20">15.2 甲方发布的相应借款需求在发出需求后二十四（24）小时内并且在此期间筹款金额未达到借款需求时，甲方有权通过主动结束筹款的方式单方终止本协议。甲方将本协议下全部本金、利息、逾期罚息、违约金及其他相关费用全部偿还完毕之时，本协议亦自动终止。</p>
    <p class="mb20">15.3 本协议的任何修改、补充均须以丙方平台电子文本形式作出。</p>
    <p class="mb20">15.4 各方均确认，本协议的签订、生效和履行以不违反法律为前提。如果本协议中的任何一条或多条违反适用的法律，则该条将被视为无效，但该无效条款并不影响本协议其他条款的效力。</p>
    <p class="mb20">15.5 各方委托丙方保管所有与本协议有关的书面文件或电子信息。</p>
    <p class="mb20"><strong>二、信用咨询服务</strong></p>
    <p class="mb20">1 甲方信息的提供和收集</p>
    <p class="mb20">1.1甲方同意，按照以下要求向丙方提供甲方信息：</p>
    <p class="mb20">(1) 丙方有权要求甲方向其提供各类甲方信息，甲方应及时提供，并应确保其提供的信息真实、完整、准确。如已经提供的甲方信息发生任何变更，甲方应在发生变更之日起的三（3）日内通知丙方，并应丙方要求提供相关证明文件。</p>
    <p class="mb20">(2) 如因甲方违反上述规定，提供的信息不真实、不完整、不准确、不及时和/或未及时在各类信息发生变更后及时通知丙方并提供证明文件，所造成的一切损失和责任由甲方承担，丙方同时有权立即停止为甲方提供服务并视情况限制或禁止甲方使用丙方平台的全部或部分功能。</p>
    <p class="mb20">1.2 授权收集甲方信息</p>
    <p class="mb20">甲方在此不可撤销地授权丙方自行和/或通过其合作的第三方机构搜集、查验各项甲方信息。</p>
    <p class="mb20">2 甲方信用评级及查询</p>
    <p class="mb20">丙方将不定期地依据其获得的甲方基本信息和/或甲方信用信息，按照其自行制订的评级规则或委托第三方合作机构对甲方及甲方特定的借款需求进行评级，并在丙方平台上公示该等评级信息。</p>
    <p class="mb20">3 甲方信息的使用</p>
    <p class="mb20">3.1甲方同意丙方按照如下规则使用及公开其各项甲方信息：</p>
    <p class="mb20">(1) 受限于第1.3.1（5）及（6）条的规定，当甲方为借款人时，未经甲方授权，丙方不会在丙方平台公示任何甲方的姓名、身份证号码、联系电话；对甲方每项借款需求，丙方平台将以匿名方式公示。</p>
    <p class="mb20">(2) 为促成甲方与出借人签署借款协议之目的，丙方可向出借人及潜在的出借人提供除甲方姓名、身份证号码及联系电话外的其他甲方信息供其在作出是否出借资金的决定时参考，但丙方与出借人签署《先花一亿元信用咨询及居间服务协议（三方）》中应按一般行业标准对甲方信息的保护作出规定。</p>
    <p class="mb20">(3) 为向甲方、其他借款人及出借人提供服务，丙方可向其第三方合作机构披露全部或部分甲方信息，但丙方与该等第三方合作机构签署的合作协议中应按一般行业标准对甲方信息的保护作出规定。</p>
    <p class="mb20">(4) 丙方可在业务运营中的数据分析、行业研究、市场推广、外部商业合作等情形下整体使用甲方信息；丙方有权对甲方信息进行分析并形成用户信用数据库，丙方对该等用户信用数据库享有完整的所有权，丙方自用该等信用数据库或将该等信用数据库提供给第三方使用均无需取得甲方的同意，亦无需向甲方支付任何费用。</p>
    <p class="mb20">(5) 如甲方违反本协议或甲方与出借人签署的借款协议，丙方有权向出借人及其授权的第三方提供甲方信息，以供出借人催收借款，同时丙方有权将甲方列入其自行制定的“黑名单”，并在丙方平台、电视、报纸及其他媒体等对甲方信息及违约事实进行公示，向甲方的联系人（包括但不限于甲方手机通讯录中不时更新的联系人、该等联系人手机中不时更新的联系人、甲方提供的社交账号的联系人或群体）告知甲方的违约事实，以及向第三方合作机构提供甲方信息，以促使甲方履行本协议和/或借款协议。</p>
    <p class="mb20">(6) 在行政、司法等政府机构要求时，丙方可向该等政府机构提供甲方信息。</p>
    <p class="mb20">3.2双方同意，丙方应根据本条规定使用甲方信息，丙方按照本条规定使用甲方信息的，无需对因丙方获取、使用、公开甲方信息而给甲方造成的任何损失承担独立的或连带的赔偿责任，包括但不限于因出借人或第三方合作机构不当使用甲方信息的行为给甲方造成的任何损失。</p>
    <p class="mb20"><strong>三、居间及相关管理服务</strong></p>
    <p class="mb20">1 丙方根据本协议为甲方之资金借入需求提供以下服务：</p>
    <p class="mb20">(1) 居间服务：甲方授权丙方在丙方平台或丙方的关联公司及丙方展开个人借款业务的合作伙伴平台上对甲方的资金需求进行展示，并通过丙方平台撮合甲方与一个或多个出借人处借入资金，最终达成借贷交易、签署借款协议；</p>
    <p class="mb20">(2) 资金划转托管服务：丙方为出借人与甲方之间借款协议项下资金的划转（包括出借人出借给甲方和甲方向出借人还款）提供托管服务；</p>
    <p class="mb20">(3) 还款提醒服务：丙方在每月还款日前通过向甲方手机（以甲方届时在丙方平台使用的手机号码为准）发送手机短信和站内信提醒的方式提醒甲方按时还款。</p>
    <p class="mb20">2 资金借入及偿还的流程</p>
    <p class="mb20">2.1 当丙方在丙方平台或丙方的关联公司及丙方展开个人借款业务的合作伙伴平台上对甲方的资金需求进行展示时，即视为甲方通过丙方平台向出借人发出不可撤销的借款要约。</p>
    <p class="mb20">2.2 双方同意，当丙方对甲方借款申请通过审核确认后，甲方与出借人订立的借款协议即告成立，丙方通过代为划扣其在合作银行或机构开立的、资金独立于丙方自有资金的托管账户（以下称“托管账户”）中出借人个人虚拟账户项下资金的方式向丙方指定的以下银行账户划转其借入的资金，甲方须确保如下银行账户为甲方名下合法有效的银行账户，甲方变更该账户时必须以电话（以甲方届时在丙方平台使用的手机号码为准）、站内信向丙方发出通知、并经丙方确认后方可变更；如因甲方未及时书面通知丙方而引发的损失由甲方自行承担。</p>
    <p class="mb20">户名（与甲方姓名一致）：<?php echo $loaninfo['realname']; ?></p>
    <p class="mb20">开户银行：（精确到支行）：<?php echo $loaninfo['bank_name']; ?></p>
    <p class="mb20">账号：<?php echo $loaninfo['card']; ?></p>
    <p class="mb20">2.3甲方在借入资金后应按照相应借款协议之约定按期还本付息。甲方应按照借款协议约定的每月还款日向托管账户支付其当期应支付的本息之和，丙方再根据丙方与出借人之间的约定向出借人划转该等资金。托管账户的信息如下：</p>
    <p class="mb20">丙方支付宝企业账户：fqzfb@xianhuahua.com</p>
    <p class="mb20">3 甲方未在到期日前向托管账户按照相应借款协议之约定缴付当期本息（包含服务费）的，视为甲方违反借款协议及本协议的约定：(1) 甲方除应尽快按期偿还借款本息外，还应按照借款协议的约定向托管账户缴付相应的逾期利息、违约金等；(2) 丙方还有权采取以下措施督促甲方尽快履行借款协议，包括但不限于将甲方列入其自行制定的“黑名单”并公示、向出借人提供甲方信息以供出借人催收、向甲方的联系人（包括但不限于甲方手机通讯录中不时更新的联系人、该等联系人手机中不时更新的联系人、甲方提供的社交账号的联系人或群体）告知甲方的违约事实、通过第三方合作机构催收借款等。</p>
    <p class="mb20"><strong>四、服务费</strong></p>
    <p class="mb20">1. 就丙方向甲方提供的本协议项下服务，甲方应向丙方支付服务费。服务费具体以·丙方平台借款页面显示为准。</p>
    <p class="mb20">2. 上述服务费在甲方通过丙方向出借人借款时由甲方一次性支付。</p>
    <p class="mb20"><strong>五、声明</strong></p>
    <p class="mb20">甲方确认在同意订立本协议前已仔细阅读了本协议，对本协议的所有条款及内容已经阅悉，均无异议，并对双方的合作关系、有关权利义务和责任条款的法律含义达成充分的理解，对丙方所提示的风险有充分的了解和预期，甲方自愿接受自主借入资金的行为所产生的全部风险。</p>
    <p class="mb20"><strong>六、违约责任</strong></p>
    <p class="mb20">任何一方违反本协议的约定，使得本协议的全部或部分不能履行，均应承担违约责任，并赔偿对方因此遭受的损失（包括由此产生的诉讼费和律师费）；如双方违约，根据实际情况各自承担相应的责任。违约方应赔偿因其违约而给守约方造成的损失，包括合同履行后可以获得的利益，但不得超过违反合同一方订立合同时可以预见或应当预见的因违反合同可能造成的损失。</p>
    <p class="mb20"><strong>七、其他</strong></p>
    <p class="mb20">1. 双方同意并确认本协议以电子合同的方式订立，在甲方在丙方平台点击“借款”之时生效，至甲方不再使用丙方所提供之服务，或甲方已经在丙方平台注销账户且双方已将所有的债权债务关系履行完毕时协议终止。</p>
    <p class="margin50">2. 本协议中的任何条款或部分条款因违反中国法律而无效的，不影响本协议其他条款的效力。</p>

</div>