# -*- coding: utf-8 -*-
# sqlacodegen  mysql://root:123!@#@127.0.0.1/xhh_antifraud --outfile anti_fraud.py --flask
from .af_base import AfBase
from .af_address import AfAddress
from .af_contact import AfContact
from .af_detail import AfDetail
from .af_detailother import AfDetailOther
from .af_report import AfReport
from .af_ss_report import AfSsReport
from .af_dbagent import AfDbAgent
from .af_addrloan import AfAddrloan
from .af_complute_rule import AfCompluteRule
from .af_result import AfResult
from .af_wsm import AfWsm
from .af_jcard_match import AfJcardMatch
from .af_relation_match import AfRelationMatch
from .af_taginfo import TagInfo