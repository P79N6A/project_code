from .addrloan import Addrloan
from .model import YiFavoriteContact
from .model import YiAntiFraud
from .model import YiUser
from .model import YiAddressList
from .model import YiLoan
from .model import YiFriend
from .model import YiUserInvest
from .model import YiUserRemitList

def relatives_is_overdue(relatives):
    '''
    亲属联系人与是否逾期
    '''
    oFC = YiFavoriteContact()
    contact_due_data = oFC.contactDue(relatives)
    return contact_due_data



