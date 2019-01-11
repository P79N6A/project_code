from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException
from pyquery import PyQuery as pq
import re

browser = webdriver.Chrome()
wait = WebDriverWait(browser, 10)
def search(i):
    try:
        i += 1
        browser.get('https://m.jd.com/#sksLeft=0&st=0')
        wait=WebDriverWait(browser, 10)
#         #去掉广告
#         if i == 1:
#             ad = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR,"div.wx_pop_bnr_box_1 > div.j_close_curtain")))
#             ad.click()
            #点击input
        wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR,"#msKeyWord"))).click()
        input = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "#msKeyWord")))
        submit = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR,'#msSearchBtn>span')))
        input.send_keys(u'手机')
        submit.click()
        if i == 1:
            #直接访
            submit = wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR,'#pcprompt-viewpc')))
            submit.click()
#         total = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR,'#itemList')))
        html=browser.page_source
        doc=pq(html)
        items=doc('#itemList').items()
        print(items)
        for item in items:
            print(item)
#         productlist = []
#         for key,item in items:
#             product={
#                 'title':item.find('div.search_prolist_title').text(),
#                 'price':item.find('div.search_prolist_price').text(),
#                 'image_url':item.find('div.search_prolist_cover>img').attr("src"),
#             }
#             productlist.append(product)
#         print(productlist)
    except TimeoutException as e:
        print(e)
        
def main():
    total = search(0)

if __name__ == '__main__':
    main()