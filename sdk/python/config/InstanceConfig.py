############################################################################
#
#  Copyright 2023 Paphus Solutions Inc.
#
#  Licensed under the Eclipse Public License, Version 1.0 (the "License");
#  you may not use this file except in compliance with the License.
#  You may obtain a copy of the License at
#
#      http://www.eclipse.org/legal/epl-v10.html
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS,
#  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#  See the License for the specific language governing permissions and
#  limitations under the License.
#
############################################################################


from config.WebMediumConfig import WebMediumConfig
from util.Utils import Writer, Utils

class InstanceConfig(WebMediumConfig):
    size: str = None
    instanceAvatar: str = None
    
    allowedForking: bool = False
    hasAPI: bool = False
    template: bool = False
    rank: int = None
    wins: int = None
    losses: int = None
    def __init__(self):
        super().__init__()
    
    def getType(self) -> str:
        return "instance"
    
    def stats(self) -> str:
        return self.connects + " connects, " + self.dailyConnects + " today " + self.weeklyConnects + " weeks, " + self.monthlyConnects + " month"
    
    def credentials(self):
        config = InstanceConfig()
        config.id = self.id
        return config
    
    def toXML(self) -> str:
        writer = Writer("<instance")
        writer.append(" allowFokring=\"" + "true" if self.allowedForking else "false" + "\"")
        writer.append(" instanceAvatar=\"" + "true" if self.instanceAvatar else "false" + "\"")
        self.writeXML(writer)
        if self.template != None:
            writer.append("<template>")
            writer.append(self.template)
            writer.append("</template>")
        
        writer.append("</instance>")
        return writer
    
    
    def parseXML(self, xml):
        super().parseXML(xml)
        root = Utils.loadXML(xml)
        if(root == None):
            return
        self.allowedForking = root.attrib.get("allowedForking")
        self.hasAPI = root.attrib.get("hasAPI")
        self.size = root.attrib.get("size")
        self.instace = root.attrib.get("instanceAvatar")
        if(root.attrib.get("rank")!=None):
            self.rank = int(root.attrib.get("rank"))
        
        if(root.attrib.get("wins")!=None):
            self.wins = int(root.attrib.get("wins"))
        
        if(root.attrib.get("losses")!=None):
            self.losses = int(root.attrib.get("losses"))
            
        if(root.attrib.get("template")!=None):
            self.template = str(root.attrib.get("template"))

    